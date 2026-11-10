import java.util.Properties

plugins {
    id("com.android.application")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "id.unsika.siaga_warga_app"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    defaultConfig {
        applicationId = "id.unsika.siaga_warga_app"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        // Android 6+: WebView modern, hampir semua HP warga terpakai
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    // Kunci rilis dibaca dari android/key.properties (tidak ikut masuk git).
    // Bila berkas itu belum ada, APK rilis ditandatangani kunci debug agar tetap bisa dipasang.
    val berkasKunci = rootProject.file("key.properties")
    val propertiKunci = Properties()
    val adaKunci = berkasKunci.exists()
    if (adaKunci) {
        berkasKunci.inputStream().use { propertiKunci.load(it) }
    }

    signingConfigs {
        if (adaKunci) {
            create("rilis") {
                storeFile = rootProject.file(propertiKunci.getProperty("storeFile"))
                storePassword = propertiKunci.getProperty("storePassword")
                keyAlias = propertiKunci.getProperty("keyAlias")
                keyPassword = propertiKunci.getProperty("keyPassword")
            }
        }
    }

    buildTypes {
        release {
            signingConfig = if (adaKunci) {
                signingConfigs.getByName("rilis")
            } else {
                signingConfigs.getByName("debug")
            }
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro",
            )
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget = org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17
    }
}

flutter {
    source = "../.."
}
