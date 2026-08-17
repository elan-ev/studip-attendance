# ElanAttendancePlugin for Stud.IP

## Voraussetzungen
Bevor Sie das Plugin installieren oder entwickeln, stellen Sie sicher, dass folgende Werkzeuge auf Ihrem System installiert sind:
* Node.js
* npm
* Composer

## Installation für Administratoren

Wenn Sie ein fertiges Release-Paket (ZIP-Datei) haben, können Sie dieses direkt über die Plugin-Verwaltung in Stud.IP hochladen.

Falls Sie das Plugin aus dem Quellcode installieren möchten:
1. Klonen Sie das Repository in das Verzeichnis public/plugins_packages/elan-ev/StudipCheckin Ihrer Stud.IP-Installation.
2. Navigieren Sie in das Plugin-Verzeichnis.
3. Führen Sie den kombinierten Build-Befehl aus:
```
npm run prezip
```
Dieser Befehl installiert alle Node-Abhängigkeiten, baut die JavaScript-Assets im dist-Ordner und installiert die PHP-Vendor-Bibliotheken via Composer.
4. Aktivieren Sie das Plugin in der Stud.IP-Plugin-Verwaltung.

## Entwicklung
Um aktiv am Plugin zu arbeiten, können Sie den Development-Modus nutzen.

### PHP-Abhängigkeiten
Stellen Sie sicher, dass die Composer-Autoload-Dateien vorhanden sind:
```
composer install
```
### Frontend (Vue/Vite)
1. Installieren Sie die Abhängigkeiten:
```
npm install
```
2. Starten Sie den Watch-Modus für die Assets:
```
npm run dev
```
Vite überwacht nun Änderungen im src-Verzeichnis und baut die Dateien im dist-Ordner automatisch neu.

### Internationalisierung (i18n)
Das Plugin nutzt vue3-gettext für Übersetzungen. Um neue Texte zu extrahieren oder zu kompilieren:
* Texte extrahieren: npm run gettext:extract (Sucht nach neuen $gettext-Aufrufen im Code).
* Übersetzungen kompilieren: npm run gettext:compile (Wandelt die .po-Dateien in für Vue lesbare Formate um).

### Paketierung
Um ein installationsfertiges ZIP-Archiv für den Upload in andere Stud.IP-Systeme zu erstellen, nutzen Sie:
```
npm run zip
```
Die fertige Datei finden Sie anschließend im Hauptverzeichnis des Plugins.

## Lizenz

Dieses Projekt ist unter der GPL-3.0 lizenziert. Weitere Details finden Sie in der LICENSE-Datei.
In der Datei LICENSE-SUPPLEMENT finden Sie weitere Bedingungen gem. Abschnitt 7 der GPL-3.0
