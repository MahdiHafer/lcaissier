package com.lcaissier.pos

import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.hardware.usb.UsbDevice
import android.hardware.usb.UsbManager
import android.os.Build
import android.os.Bundle
import android.util.Base64
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.EditText
import android.widget.ImageButton
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var settingsButton: ImageButton
    private lateinit var usbManager: UsbManager
    private lateinit var printer: UsbEscPosPrinter

    private var pendingBytes: ByteArray? = null

    companion object {
        private const val ACTION_USB_PERMISSION = "com.lcaissier.pos.USB_PERMISSION"
        private const val PREFS_NAME = "lcaissier_prefs"
        private const val PREF_POS_URL = "pos_url"
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
            val device = printer.findPrinterDevice()
            if (device == null) {
                toast("Imprimante introuvable")
                return
            }

            val result = printer.printDetailed(device, data)
            toast(result.message)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        usbManager = getSystemService(Context.USB_SERVICE) as UsbManager
        printer = UsbEscPosPrinter(usbManager)

        @Suppress("DEPRECATION")
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            registerReceiver(usbPermissionReceiver, IntentFilter(ACTION_USB_PERMISSION), RECEIVER_NOT_EXPORTED)
        } else {
            registerReceiver(usbPermissionReceiver, IntentFilter(ACTION_USB_PERMISSION))
        }

        webView = findViewById(R.id.posWebView)
        settingsButton = findViewById(R.id.btnSettingsUrl)
        webView.settings.javaScriptEnabled = true
        webView.settings.domStorageEnabled = true
        webView.settings.allowFileAccess = false
        webView.settings.allowContentAccess = false
        webView.settings.mixedContentMode = WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE
        webView.webChromeClient = WebChromeClient()
        webView.webViewClient = object : WebViewClient() {
            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                super.onReceivedError(view, request, error)
                if (request?.isForMainFrame == true) {
                    showLoadError(
                        "Chargement impossible",
                        "URL: ${getPosUrl()}\nErreur: ${error?.description ?: "Inconnue"}"
                    )
                }
            }
        }

        webView.addJavascriptInterface(AndroidPrinterBridge(), "AndroidPrinter")
        settingsButton.setOnClickListener {
            openUrlDialog()
        }
        webView.loadUrl(getPosUrl())
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

    private fun printEscPos(bytes: ByteArray): PrintAttempt {
        val device = printer.findPrinterDevice()
            ?: return PrintAttempt(false, "Aucune imprimante USB detectee. ${printer.describeDevices()}")

        if (!usbManager.hasPermission(device)) {
            pendingBytes = bytes
            requestUsbPermission(device)
            return PrintAttempt(true, "Permission USB demandee. Autorisez puis relancez l'impression.")
        }

        val result = printer.printDetailed(device, bytes)
        return PrintAttempt(result.success, result.message)
    }

    private fun toast(message: String) {
        runOnUiThread {
            Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
        }
    }

    private fun showLoadError(title: String, details: String) {
        val safeTitle = title.replace("<", "&lt;").replace(">", "&gt;")
        val safeDetails = details.replace("<", "&lt;").replace(">", "&gt;").replace("\n", "<br>")
        val html = """
            <html><body style="font-family:sans-serif;padding:16px;background:#fff;">
              <h2 style="margin:0 0 8px 0;color:#111;">$safeTitle</h2>
              <p style="color:#444;line-height:1.5;">$safeDetails</p>
              <p style="color:#666;">Verifiez que le PC et le telephone sont sur le meme Wi-Fi et que Laravel est lance avec:<br><code>php artisan serve --host=0.0.0.0 --port=8000</code></p>
            </body></html>
        """.trimIndent()
        webView.loadDataWithBaseURL(null, html, "text/html", "utf-8", null)
    }

    private fun getPosUrl(): String {
        val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        return prefs.getString(PREF_POS_URL, BuildConfig.POS_URL) ?: BuildConfig.POS_URL
    }

    private fun savePosUrl(url: String) {
        val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        prefs.edit().putString(PREF_POS_URL, url).apply()
    }

    private fun openUrlDialog() {
        val input = EditText(this).apply {
            setText(getPosUrl())
            hint = "http://192.168.x.x:8000/caisse"
            setSingleLine(true)
            setPadding(36, 24, 36, 24)
        }

        AlertDialog.Builder(this)
            .setTitle("URL du serveur POS")
            .setMessage("Change l'adresse du serveur puis recharge l'application.")
            .setView(input)
            .setNegativeButton("Annuler", null)
            .setNeutralButton("Defaut") { _, _ ->
                savePosUrl(BuildConfig.POS_URL)
                webView.loadUrl(getPosUrl())
                toast("URL par defaut restauree")
            }
            .setPositiveButton("Enregistrer") { _, _ ->
                val raw = input.text?.toString()?.trim().orEmpty()
                if (raw.isBlank()) {
                    toast("URL vide")
                    return@setPositiveButton
                }

                val normalized = normalizeUrl(raw)
                if (normalized == null) {
                    toast("URL invalide")
                    return@setPositiveButton
                }

                savePosUrl(normalized)
                webView.loadUrl(normalized)
                toast("URL enregistree")
            }
            .show()
    }

    private fun normalizeUrl(url: String): String? {
        val prefixed = if (url.startsWith("http://") || url.startsWith("https://")) url else "http://$url"
        return try {
            val uri = android.net.Uri.parse(prefixed)
            if (uri.scheme.isNullOrBlank() || uri.host.isNullOrBlank()) null else prefixed
        } catch (_: Exception) {
            null
        }
    }

    data class PrintAttempt(
        val accepted: Boolean,
        val message: String
    )

    inner class AndroidPrinterBridge {
        @JavascriptInterface
        fun printEscPos(base64: String): Boolean {
            val status = printEscPosStatus(base64)
            return status.startsWith("OK|")
        }

        @JavascriptInterface
        fun printEscPosStatus(base64: String): String {
            return try {
                val bytes = Base64.decode(base64, Base64.DEFAULT)
                val attempt = printEscPos(bytes)
                val prefix = if (attempt.accepted) "OK" else "ERR"
                "$prefix|${attempt.message}"
            } catch (e: Exception) {
                "ERR|Erreur impression: ${e.message ?: "inconnue"}"
            }
        }
    }
}
