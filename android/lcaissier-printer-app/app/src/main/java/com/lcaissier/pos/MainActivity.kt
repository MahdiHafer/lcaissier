package com.lcaissier.pos

import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.hardware.usb.UsbConstants
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbManager
import android.os.Build
import android.os.Bundle
import android.util.Base64
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var usbManager: UsbManager
    private lateinit var printer: UsbEscPosPrinter

    private var pendingBytes: ByteArray? = null

    companion object {
        private const val ACTION_USB_PERMISSION = "com.lcaissier.pos.USB_PERMISSION"
    }

    private val usbPermissionReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context, intent: Intent) {
            if (intent.action != ACTION_USB_PERMISSION) return
            val granted = intent.getBooleanExtra(UsbManager.EXTRA_PERMISSION_GRANTED, false)
            if (!granted) {
                toast("Permission USB refusee")
                return
            }

            val data = pendingBytes ?: return
            pendingBytes = null
            val device = printer.findPrinterDevice() ?: run {
                toast("Imprimante introuvable")
                return
            }

            val ok = printer.print(device, data)
            toast(if (ok) "Impression envoyee" else "Erreur impression")
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        usbManager = getSystemService(Context.USB_SERVICE) as UsbManager
        printer = UsbEscPosPrinter(this, usbManager)

        @Suppress("DEPRECATION")
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            registerReceiver(usbPermissionReceiver, IntentFilter(ACTION_USB_PERMISSION), RECEIVER_NOT_EXPORTED)
        } else {
            registerReceiver(usbPermissionReceiver, IntentFilter(ACTION_USB_PERMISSION))
        }

        webView = findViewById(R.id.posWebView)
        webView.settings.javaScriptEnabled = true
        webView.settings.domStorageEnabled = true
        webView.settings.allowFileAccess = false
        webView.settings.allowContentAccess = false
        webView.webChromeClient = WebChromeClient()
        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
            }
        }

        webView.addJavascriptInterface(AndroidPrinterBridge(), "AndroidPrinter")
        webView.loadUrl(BuildConfig.POS_URL)
    }

    override fun onDestroy() {
        super.onDestroy()
        unregisterReceiver(usbPermissionReceiver)
    }

    private fun requestUsbPermission(device: UsbDevice) {
        val flags = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) PendingIntent.FLAG_IMMUTABLE else 0
        val pi = PendingIntent.getBroadcast(this, 0, Intent(ACTION_USB_PERMISSION), flags)
        usbManager.requestPermission(device, pi)
    }

    private fun printEscPos(bytes: ByteArray): Boolean {
        val device = printer.findPrinterDevice() ?: return false

        if (!usbManager.hasPermission(device)) {
            pendingBytes = bytes
            requestUsbPermission(device)
            return true
        }

        return printer.print(device, bytes)
    }

    private fun toast(message: String) {
        runOnUiThread {
            Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
        }
    }

    inner class AndroidPrinterBridge {
        @JavascriptInterface
        fun printEscPos(base64: String): Boolean {
            return try {
                val bytes = Base64.decode(base64, Base64.DEFAULT)
                val ok = printEscPos(bytes)
                if (!ok) toast("Imprimante non connectee")
                ok
            } catch (e: Exception) {
                toast("Erreur impression: ${e.message}")
                false
            }
        }
    }
}
