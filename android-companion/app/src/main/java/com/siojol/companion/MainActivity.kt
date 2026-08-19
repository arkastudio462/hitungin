package com.siojol.companion

import android.annotation.SuppressLint
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.os.Environment
import android.provider.Settings
import android.webkit.JavascriptInterface
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebResourceError
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.ComponentActivity
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.core.content.FileProvider
import com.siojol.companion.prefs.UserPrefs
import java.io.File
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class MainActivity : ComponentActivity() {

    private lateinit var prefs: UserPrefs
    private var _webView: WebView? = null
    private var fileChooserCallback: ValueCallback<Array<Uri>>? = null
    private var cameraImageUri: Uri? = null

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        prefs = UserPrefs(this)

        setContent {
            val context = LocalContext.current

            val filePickerLauncher = rememberLauncherForActivityResult(
                contract = ActivityResultContracts.StartActivityForResult()
            ) { result ->
                val uris = if (result.resultCode == RESULT_OK) {
                    result.data?.let { intent ->
                        intent.clipData?.let { clip ->
                            Array(clip.itemCount) { clip.getItemAt(it).uri }
                        } ?: intent.data?.let { arrayOf(it) }
                    }
                } else null

                fileChooserCallback?.onReceiveValue(uris)
                fileChooserCallback = null
            }

            var isLoading by remember { mutableStateOf(true) }
            var loadError by remember { mutableStateOf<String?>(null) }

            MaterialTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    Column(modifier = Modifier.fillMaxSize()) {
                        Box(modifier = Modifier.weight(1f)) {
                            AndroidView(
                                modifier = Modifier.fillMaxSize(),
                                factory = { context ->
                                    WebView(context).apply {
                                        _webView = this
                                        settings.javaScriptEnabled = true
                                        settings.domStorageEnabled = true
                                        settings.databaseEnabled = true
                                        settings.cacheMode = WebSettings.LOAD_DEFAULT
                                        settings.setSupportMultipleWindows(false)
                                        settings.userAgentString = "Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36"
                                        settings.allowFileAccess = true
                                        WebView.setWebContentsDebuggingEnabled(true)

                                        webChromeClient = object : WebChromeClient() {
                                            override fun onShowFileChooser(
                                                webView: WebView?,
                                                filePathCallback: ValueCallback<Array<Uri>>?,
                                                fileChooserParams: FileChooserParams?
                                            ): Boolean {
                                                fileChooserCallback?.onReceiveValue(null)
                                                fileChooserCallback = filePathCallback

                                                val cameraIntent = Intent(android.provider.MediaStore.ACTION_IMAGE_CAPTURE)
                                                val photoFile = createImageFile()
                                                if (photoFile != null) {
                                                    cameraImageUri = FileProvider.getUriForFile(
                                                        context,
                                                        "${context.packageName}.fileprovider",
                                                        photoFile
                                                    )
                                                    cameraIntent.putExtra(android.provider.MediaStore.EXTRA_OUTPUT, cameraImageUri)
                                                }

                                                val galleryIntent = Intent(Intent.ACTION_GET_CONTENT).apply {
                                                    type = "image/*"
                                                    addCategory(Intent.CATEGORY_OPENABLE)
                                                    putExtra(Intent.EXTRA_ALLOW_MULTIPLE, true)
                                                }

                                                val chooserIntent = Intent.createChooser(galleryIntent, "Pilih gambar")
                                                chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, arrayOf(cameraIntent))

                                                filePickerLauncher.launch(chooserIntent)
                                                return true
                                            }
                                        }

                                        webViewClient = object : WebViewClient() {
                                            override fun shouldOverrideUrlLoading(
                                                view: WebView?,
                                                request: WebResourceRequest?
                                            ): Boolean {
                                                val url = request?.url?.toString()
                                                if (url != null && !url.startsWith(prefs.baseUrl)) {
                                                    startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
                                                    return true
                                                }
                                                return false
                                            }

                                            override fun onPageFinished(view: WebView?, url: String?) {
                                                super.onPageFinished(view, url)
                                                isLoading = false
                                                loadError = null
                                                injectViewportHeight()
                                                injectTokenSync()
                                            }

                                            override fun onReceivedError(
                                                view: WebView?,
                                                request: WebResourceRequest?,
                                                error: WebResourceError?
                                            ) {
                                                super.onReceivedError(view, request, error)
                                                if (request?.isForMainFrame == true) {
                                                    loadError = error?.description?.toString() ?: "Gagal memuat halaman."
                                                }
                                            }
                                        }

                                        addJavascriptInterface(TokenBridge(), "AndroidBridge")

                                        loadUrl("${prefs.baseUrl}/app")
                                    }
                                }
                            )

                            if (isLoading && loadError == null) {
                                Box(
                                    modifier = Modifier.fillMaxSize(),
                                    contentAlignment = Alignment.Center
                                ) {
                                    CircularProgressIndicator()
                                }
                            }

                            if (loadError != null) {
                                Box(
                                    modifier = Modifier.fillMaxSize(),
                                    contentAlignment = Alignment.Center
                                ) {
                                    Column(
                                        horizontalAlignment = Alignment.CenterHorizontally,
                                        modifier = Modifier.padding(24.dp)
                                    ) {
                                        Text(
                                            text = "Gagal memuat aplikasi.",
                                            style = MaterialTheme.typography.titleMedium
                                        )
                                        Text(
                                            text = loadError ?: "",
                                            style = MaterialTheme.typography.bodySmall,
                                            modifier = Modifier.padding(vertical = 8.dp)
                                        )
                                        Button(onClick = {
                                            loadError = null
                                            isLoading = true
                                            _webView?.reload()
                                        }) {
                                            Text("Coba Lagi")
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private fun createImageFile(): File? {
        val timeStamp = SimpleDateFormat("yyyyMMdd_HHmmss", Locale.getDefault()).format(Date())
        val storageDir = getExternalFilesDir(Environment.DIRECTORY_PICTURES)
        return File.createTempFile("RECEIPT_${timeStamp}_", ".jpg", storageDir)
    }

    private fun injectViewportHeight() {
        _webView?.evaluateJavascript(
            """
            (function() {
                function setVh() {
                    var vh = window.innerHeight * 0.01;
                    document.documentElement.style.setProperty('--vh', vh + 'px');
                }
                setVh();
                window.addEventListener('resize', setVh);
                window.addEventListener('orientationchange', function() {
                    setTimeout(setVh, 100);
                });
            })();
            """.trimIndent(), null
        )
    }

    private fun injectTokenSync() {
        _webView?.evaluateJavascript(
            """
            (function() {
                function sync() {
                    try {
                        var token = localStorage.getItem('token');
                        window.AndroidBridge.syncToken(token);
                    } catch (e) {}
                    setTimeout(sync, 2000);
                }
                sync();
            })();
            """.trimIndent(), null
        )
    }

    private inner class TokenBridge {
        @JavascriptInterface
        fun syncToken(token: String?) {
            runOnUiThread {
                if (token.isNullOrEmpty()) {
                    prefs.clear()
                } else {
                    prefs.token = token
                    prefs.isLoggedIn = true
                }
            }
        }

        @JavascriptInterface
        fun isNotificationListenerEnabled(): Boolean {
            return try {
                val cn = android.content.ComponentName(this@MainActivity, NotificationListener::class.java)
                val flat = Settings.Secure.getString(
                    this@MainActivity.contentResolver,
                    "enabled_notification_listeners"
                )
                flat?.contains(cn.flattenToString()) == true
            } catch (e: Exception) {
                false
            }
        }

        @JavascriptInterface
        fun openNotificationSettings() {
            runOnUiThread {
                startActivity(Intent(Settings.ACTION_NOTIFICATION_LISTENER_SETTINGS))
            }
        }
    }
}
