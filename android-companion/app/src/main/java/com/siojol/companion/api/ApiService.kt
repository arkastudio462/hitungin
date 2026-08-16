package com.siojol.companion.api

import com.siojol.companion.model.LoginResponse
import com.siojol.companion.model.NotificationForward
import retrofit2.Response
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.Header
import retrofit2.http.POST

interface ApiService {

    @FormUrlEncoded
    @POST("api/login")
    suspend fun login(
        @Field("email") email: String,
        @Field("password") password: String
    ): Response<LoginResponse>

    @FormUrlEncoded
    @POST("api/notification-forward")
    suspend fun forwardNotification(
        @Header("Authorization") token: String,
        @Header("X-Api-Key") apiKey: String,
        @Field("package_name") packageName: String,
        @Field("title") title: String?,
        @Field("message") message: String
    ): Response<NotificationForward>
}
