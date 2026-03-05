package com.lcaissier.pos

import android.hardware.usb.UsbConstants
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbEndpoint
import android.hardware.usb.UsbInterface
import android.hardware.usb.UsbManager

class UsbEscPosPrinter(
    private val usbManager: UsbManager
) {
    data class PrintResult(
        val success: Boolean,
        val message: String
    )

    fun findPrinterDevice(): UsbDevice? {
        val devices = usbManager.deviceList.values.toList()

        // Prefer a true USB printer class device.
        val classPrinter = devices.firstOrNull { device ->
            (0 until device.interfaceCount).any { i ->
                device.getInterface(i).interfaceClass == UsbConstants.USB_CLASS_PRINTER
            }
        }
        if (classPrinter != null) return classPrinter

        // Fallback for chipsets exposing vendor class but still having bulk OUT endpoint.
        return devices.firstOrNull { hasBulkOutEndpoint(it) }
    }

    fun describeDevices(): String {
        val devices = usbManager.deviceList.values.toList()
        if (devices.isEmpty()) return "Aucun appareil USB detecte via OTG."

        return devices.joinToString(" ; ") { device ->
            val name = (device.productName ?: device.deviceName ?: "USB").trim()
            val hasOut = hasBulkOutEndpoint(device)
            "$name (VID:${device.vendorId}, PID:${device.productId}, bulkOut:${if (hasOut) "oui" else "non"})"
        }
    }

    fun print(device: UsbDevice, payload: ByteArray): Boolean {
        return printDetailed(device, payload).success
    }

    fun printDetailed(device: UsbDevice, payload: ByteArray): PrintResult {
        val conn = usbManager.openDevice(device)
            ?: return PrintResult(false, "Impossible d'ouvrir le peripherique USB.")
        try {
            val usbInterface = chooseInterface(device)
                ?: return PrintResult(false, "Interface imprimante introuvable sur ce peripherique.")
            if (!conn.claimInterface(usbInterface, true)) {
                return PrintResult(false, "Impossible de prendre la main sur l'interface USB.")
            }

            val out = findBulkOut(usbInterface)
                ?: return PrintResult(false, "Endpoint bulk OUT non trouve.")

            var offset = 0
            val chunkSize = 2048
            while (offset < payload.size) {
                val len = minOf(chunkSize, payload.size - offset)
                val chunk = payload.copyOfRange(offset, offset + len)
                val sent = conn.bulkTransfer(out, chunk, chunk.size, 3000)
                if (sent <= 0) return PrintResult(false, "Echec d'envoi USB (bulkTransfer=$sent).")
                offset += sent
            }

            return PrintResult(true, "Ticket envoye (${payload.size} octets).")
        } finally {
            conn.close()
        }
    }

    private fun chooseInterface(device: UsbDevice): UsbInterface? {
        for (i in 0 until device.interfaceCount) {
            val intf = device.getInterface(i)
            if (intf.interfaceClass == UsbConstants.USB_CLASS_PRINTER && findBulkOut(intf) != null) {
                return intf
            }
        }

        for (i in 0 until device.interfaceCount) {
            val intf = device.getInterface(i)
            if (findBulkOut(intf) != null) {
                return intf
            }
        }

        return null
    }

    private fun hasBulkOutEndpoint(device: UsbDevice): Boolean {
        for (i in 0 until device.interfaceCount) {
            if (findBulkOut(device.getInterface(i)) != null) return true
        }
        return false
    }

    private fun findBulkOut(intf: UsbInterface): UsbEndpoint? {
        for (i in 0 until intf.endpointCount) {
            val ep = intf.getEndpoint(i)
            if (ep.type == UsbConstants.USB_ENDPOINT_XFER_BULK && ep.direction == UsbConstants.USB_DIR_OUT) {
                return ep
            }
        }
        return null
    }
}
