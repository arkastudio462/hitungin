package com.siojol.companion.model

data class NotificationForward(
    val id: Long,
    val status: String,
    val parsed: ParsedData?
)

data class ParsedData(
    val type: String,
    val amount: Double,
    val description: String,
    val date: String,
    val merchant: String?,
    val category_guess: String?
)

data class LoginResponse(
    val token: String,
    val user: User
)

data class User(
    val id: Long,
    val name: String,
    val email: String
)
