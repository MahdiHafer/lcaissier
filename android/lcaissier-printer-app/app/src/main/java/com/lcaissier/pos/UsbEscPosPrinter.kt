package com.lcaissier.pos

import android.content.Context
import android.hardware.usb.UsbConstants
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbEndpoint
import android.hardware.usb.UsbInterface
import android.hardware.usb.UsbManager
import android.hardware.usb.UsbRequest

class UsbEscPosPrinter(
    private val context: Context,
    private val usbManager: UsbManager
) {

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

    fun print(device: UsbDevice, payload: ByteArray): Boolean {
        val conn = usbManager.openDevice(device) ?: return false
        try {
            val usbInterface = chooseInterface(device) ?: return false
            if (!conn.claimInterface(usbInterface, true)) return false

            val out = findBulkOut(usbInterface) ?: return false

            var offset = 0
            val chunkSize = 2048
            while (offset < payload.size) {
                val len = minOf(chunkSize, payload.size - offset)
                val chunk = payload.copyOfRange(offset, offset + len)
                val sent = conn.bulkTransfer(out, chunk, chunk.size, 3000)
                if (sent <= 0) return false
                offset += sent
            }

            return true
        } finally {
            try {
                // no-op
            } catch (_: Exception) {
            }
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
