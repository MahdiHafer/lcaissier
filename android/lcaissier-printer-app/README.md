# L'CAISSIER - Android POS Printer App

Application Android dediee pour tablette/telephone afin de:
- ouvrir la caisse Laravel dans un WebView
- imprimer en USB OTG sur imprimante 80mm ESC/POS en 1 clic
- utiliser le bouton `Imprimer` existant dans la caisse (bridge JS Android)

## 1) URL de la caisse

Par defaut, l'app charge:
- `http://192.168.137.129:8000/caisse`

Si ton IP change, tu peux la modifier directement dans l'app:
- Ouvre l'app
- Appuie sur l'icone `parametres` en haut a droite
- Saisis la nouvelle URL (ex: `http://192.168.1.15:8000/caisse`)
- Enregistrer

Option de secours:
- Bouton `Defaut` dans le popup pour revenir a l'URL compilee dans `BuildConfig.POS_URL`.

## 2) Build APK (Android Studio)

1. Ouvrir le dossier `android/lcaissier-printer-app` dans Android Studio.
2. Laisser Gradle sync.
3. Build > Build APK(s).
4. Installer l'APK sur la tablette Android.

## 3) Prerequis reseau

- PC et tablette sur le meme Wi-Fi.
- Lancer Laravel en ecoute reseau:

```powershell
C:\php82\php.exe artisan serve --host=0.0.0.0 --port=8000
```

- Autoriser le port 8000 dans le pare-feu Windows si necessaire.

## 4) Utilisation impression USB OTG

1. Brancher l'imprimante WDLink 80mm via OTG.
2. Ouvrir l'app Android.
3. Aller sur la caisse, remplir le panier.
4. Taper `Imprimer`.
5. A la premiere impression, accepter la permission USB Android.

Ensuite l'impression part directement sans popup de selection.

## Notes techniques

- Cote web, la caisse appelle automatiquement `AndroidPrinter.printEscPos(base64)` si disponible.
- Si l'app Android n'est pas utilisee, la caisse garde le fallback impression navigateur.
- Impression attendue en mode ESC/POS compatible 80mm.

## APK release signee (production)

Un workflow GitHub est disponible:
- `.github/workflows/android-release.yml`
- Action: `Build Signed Android Release APK`

Secrets GitHub a ajouter (Settings > Secrets and variables > Actions):
- `ANDROID_KEYSTORE_BASE64`
- `ANDROID_KEYSTORE_PASSWORD`
- `ANDROID_KEY_ALIAS`
- `ANDROID_KEY_PASSWORD`

Generation keystore (une seule fois, en local):

```powershell
keytool -genkeypair -v -keystore lcaissier-release.jks -alias lcaissier -keyalg RSA -keysize 2048 -validity 10000
```

Encoder le fichier en base64 pour le secret:

```powershell
[Convert]::ToBase64String([IO.File]::ReadAllBytes("lcaissier-release.jks")) | Set-Content keystore.base64.txt
```

Ensuite:
1. Copier le contenu de `keystore.base64.txt` dans le secret `ANDROID_KEYSTORE_BASE64`.
2. Lancer l'action `Build Signed Android Release APK`.
3. Telecharger l'artifact `lcaissier-pos-release-apk` (fichier `app-release.apk`).

## Alternative sans installer Java/JDK en local

Un workflow autonome existe:
- `.github/workflows/android-release-auto-keystore.yml`
- Action: `Build Release APK (Auto Keystore)`

Ce workflow:
1. Genere un keystore automatiquement sur GitHub.
2. Compile une APK release signee.
3. Publie 2 artifacts:
   - `lcaissier-pos-release-apk-auto` (APK)
   - `lcaissier-keystore-auto` (keystore)

Important:
- Conserve absolument le fichier keystore (`lcaissier-keystore-auto`) en lieu sur.
- Le meme keystore est necessaire pour publier les futures mises a jour de l'application.
