package com.siojol.companion

import android.annotation.SuppressLint
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.provider.Settings
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebResourceError
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.safeDrawingPadding
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
import androidx.compose.ui.unit.dp
import androidx.compose.ui.viewinterop.AndroidView
import com.siojol.companion.prefs.UserPrefs

class MainActivity : ComponentActivity() {

    private lateinit var prefs: UserPrefs
    private var _webView: WebView? = null

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        prefs = UserPrefs(this)

        setContent {
            var isLoading by remember { mutableStateOf(true) }
            var loadError by remember { mutableStateOf<String?>(null) }

            MaterialTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    Column(modifier = Modifier.fillMaxSize().safeDrawingPadding()) {
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
                                        WebView.setWebContentsDebuggingEnabled(true)

                                        webChromeClient = WebChromeClient()
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
