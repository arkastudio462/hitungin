# Keuangan Companion App

Aplikasi Android pendamping untuk membaca notifikasi bank & e-wallet secara otomatis.

## Build tanpa Android Studio

### Cara 1: GitHub Actions (Recommended)

1. Push repo ke GitHub
2. Buka tab **Actions** di GitHub
3. Klik **Build APK** → **Run workflow**
4. Setelah selesai, download APK dari tab **Artifacts**

### Cara 2: Command Line

```bash
# Install JDK 17
# Ubuntu/Debian:
sudo apt install openjdk-17-jdk

# macOS:
brew install openjdk@17

# Download Gradle Wrapper
gradle wrapper --gradle-version 8.11.1

# Build APK
./gradlew assembleDebug
```

APK akan ada di: `app/build/outputs/apk/debug/app-debug.apk`

### Cara 3: Docker

```bash
docker run --rm -v $(pwd):/workspace -w /workspace \
  ghcr.io/gradle/gradle:8.11.1-jdk17 \
  ./gradlew assembleDebug
```

## Install ke HP

```bash
adb install app/build/outputs/apk/debug/app-debug.apk
```

Atau copy APK ke HP → buka file → install.

## Setup

1. Buka app → Login dengan akun Keuangan
2. Aktifkan akses notifikasi
3. Notifikasi dari bank/ewallet akan otomatis terkirim ke server
