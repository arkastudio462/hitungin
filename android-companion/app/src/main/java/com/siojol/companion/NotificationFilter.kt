package com.siojol.companion

object NotificationFilter {

    private val BANKING_APPS = mapOf(
        "com.bca" to "BCA Mobile",
        "com.bri" to "BRImo",
        "com.bni" to "BNI Mobile Banking",
        "com.mandiri" to "Livin by Mandiri",
        "com.cimb" to "CIMB Clicks",
        "com.danamon" to "Danamon Online",
        "com.bsi" to "BSI Mobile",
        "com.mega" to "Mega Mobile",
        "com.jenius" to "Jenius",
        "com.bjb" to "BJB Digi",
        "com.bpd" to "BPD Mobile",
    )

    private val EWALLET_APPS = mapOf(
        "com.gopay.gopayapp" to "GoPay",
        "com.gopajj.gopajj" to "OVO",
        "com.dana" to "DANA",
        "com.shopeepay" to "ShopeePay",
        "com.linkaja" to "LinkAja",
        "com.finpay" to "Finpay",
        "com.finplus" to "Finplus",
    )

    private val ALL_TRACKED_APPS = BANKING_APPS + EWALLET_APPS

    fun isTrackedApp(packageName: String): Boolean {
        return ALL_TRACKED_APPS.containsKey(packageName)
    }

    fun getAppName(packageName: String): String {
        return ALL_TRACKED_APPS[packageName] ?: packageName
    }

    fun isFinancialNotification(title: String?, text: String?): Boolean {
        val combined = "${title.orEmpty()} ${text.orEmpty()}".lowercase()

        val keywords = listOf(
            "transfer", "payment", "bayar", "beli", "top up", "topup",
            "masuk", "keluar", "debit", "kredit", "saldo", "balance",
            "trx", "transaksi", "refund", "cashback", "gaji", "pendapatan",
            "pengeluaran", "pembelian", "penarikan", "setor", "tarik",
            "berhasil", "success", "received", "sent", "completed",
            "rp", "idr", "rupiah",
        )

        return keywords.any { combined.contains(it) }
    }
}
