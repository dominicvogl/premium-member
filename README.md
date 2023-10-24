# Installation
* Das Plugin kann ganz regulär über den Wordpress Plugin Manager, via .zip Upload installiert werden. Das Github Repo kann hierfür einfach als .zip heruntergeladen werden.
* Nach der Aktivierung des Plugins ist unter "Einstellungen/Raidboxes Premium Member" die Konfiguration des Plugins möglich.

## Shortcodes
Folgende Shortcodes sind für die Verwendung im Frontend verfügbar:

* `[user_register_form]` - Zeigt das Formular für die Registrierung an
* `[user_detail_page]` - Zeigt die Daten des jeweiligen Benutzers an
* `[user_password_reset]` - Zeigt das Formular für das Zurücksetzen des Passworts an
* `[user_login_form]` - Zeigt das Formular für den Login an

# Reactions, Rückfragen?
* **Login Checkbox:** Ich würde den Nutzer hier nicht einfach nur nicht anmelden lassen. Habe daher um die Usability zu verbessern a) einen Callout eingebaut, wenn das Formular deaktiviert ist und b) den Button 'disabled', damit man auch das Formular gar nicht erst abschicken kann um unnötigen Server Traffic zu vermeiden.
* Ich habe eine Textdomain registriert, damit man darauf aufbauend dann Übersetzungen für das Plugin vornehmen kann. Habe ich an dieser Stelle aber mal nur für das Backend gemacht, um zu visualisieren, dass es klappt. Im Plugin sind entsprechende Strings bereits dafür vorbereitet.

## weiterführende  mögliche Optimierungen des Plugins
* Beim installieren des Plugins direkt die jeweiligen Seiten mit entsprechenden Shortcodes anlegen, somit spart man sich diese Schritte
* Im ganzen Plugin alle "raidboxes_premium_member" Labels der Textdomain noch auf eine Variable in der Klasse ändern und so einfacher für Anpassungen machen
* Für das Registrieren der Benutzer könnte man wahlweise auch die Standardoption in Wordpress verwenden, die man unter "Einstellungen/Allgemein" findet. Nachteil wäre hier dass man nicht nach der Rolle selektieren kann, aber je nach Anwendungsfall eine Option.
* Optimierung auf eine bestimmte PHP Version z.B. 8.1 oder 8.2, abhänging von der Umgebung bei der das Plugin eingesetzt werden soll und welche Anforderungen erfüllt werden sollen bzw, wie weit man ältere Versionen unterstützen will.
