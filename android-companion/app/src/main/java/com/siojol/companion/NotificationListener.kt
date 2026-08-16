package com.siojol.companion

import android.app.Notification
import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification
import android.util.Log
import com.siojol.companion.api.ApiClient
import com.siojol.companion.prefs.UserPrefs
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch

class NotificationListener : NotificationListenerService() {

    private val TAG = "NotificationListener"
    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)
    private lateinit var prefs: UserPrefs

    override fun onCreate() {
        super.onCreate()
        prefs = UserPrefs(this)
        Log.d(TAG, "NotificationListener created")
    }

    override fun onNotificationPosted(sbn: StatusBarNotification?) {
        sbn ?: return

        if (!prefs.isLoggedIn) return

        val packageName = sbn.packageName

        if (!NotificationFilter.isTrackedApp(packageName)) return

        val notification = sbn.notification
        val extras = notification.extras

        val title = extras.getCharSequence(Notification.EXTRA_TITLE)?.toString()
        val text = extras.getCharSequence(Notification.EXTRA_TEXT)?.toString()

        if (!NotificationFilter.isFinancialNotification(title, text)) return

        Log.d(TAG, "Financial notification from $packageName: $title - $text")

        sendToServer(packageName, title, text)
    }

    private fun sendToServer(packageName: String, title: String?, message: String?) {
        val token = prefs.token ?: return
        val apiKey = prefs.apiKey

        scope.launch {
            try {
                val response = ApiClient.getApi(prefs).forwardNotification(
                    token = "Bearer $token",
                    apiKey = apiKey,
                    packageName = packageName,
                    title = title,
                    message = message ?: ""
                )

                if (response.isSuccessful) {
                    Log.d(TAG, "Notification forwarded successfully: ${response.body()?.status}")
                } else {
                    Log.e(TAG, "Failed to forward notification: ${response.code()} ${response.message()}")
                }
            } catch (e: Exception) {
                Log.e(TAG, "Error forwarding notification", e)
            }
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        Log.d(TAG, "NotificationListener destroyed")
    }
}
