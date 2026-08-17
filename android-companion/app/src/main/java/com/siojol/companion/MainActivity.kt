package com.siojol.companion

import android.annotation.SuppressLint
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.provider.Settings
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.ui.Modifier
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
            MaterialTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    Column(modifier = Modifier.fillMaxSize()) {
                        AndroidView(
                            modifier = Modifier.weight(1f),
                            factory = { context ->
                                WebView(context).apply {
                                    _webView = this
                                    settings.javaScriptEnabled = true
                                    settings.domStorageEnabled = true
                                    settings.databaseEnabled = true
                                    settings.cacheMode = WebSettings.LOAD_DEFAULT
                                    settings.userAgentString = settings.userAgentString + " HitunginAndroid"

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
                                            injectTokenSync()
                                        }
                                    }

                                    addJavascriptInterface(TokenBridge(), "AndroidBridge")

                                    loadUrl("${prefs.baseUrl}/app")
                                }
                            }
                        )
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