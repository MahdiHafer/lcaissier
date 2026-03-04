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
