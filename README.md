# Anmerkungen
* **Login Checkbox:** Ich würde den Nutzer hier nicht einfach nur nicht anmelden lassen. Habe daher um die Usability zu verbessern a) einen Callout eingebaut, wenn das Formular deaktiviert ist und b) den Button 'disabled', damit man auch das Formular gar nicht erst abschicken kann um unnötigen Server Traffic zu vermeiden.
* Ich habe eine Textdomain registriert, damit man darauf aufbauend dann Übersetzungen für das Plugin vornehmen kann. Habe ich an dieser Stelle aber mal nur für das Backend gemacht, um zu visualisieren, dass es klappt. Im Plugin sind entsprechende Strings bereits dafür vorbereitet.

## Optimierungsideen
* Im ganzen Plugin alle "raidboxes_premium_member" Labels der Textdomain noch auf eine Variable in der Klasse ändern und so einfacher für Anpassungen machen
* Für das Registrieren der Benutzer könnte man wahlweise auch die Standardoption in Wordpress verwenden, die man unter "Einstellungen/Allgemein" findet. Nachteil wäre hier dass man nicht nach der Rolle selektieren kann, aber je nach Anwendungsfall eine Option.
* Optimierung auf eine bestimmte PHP Version z.B. 8.1 oder 8.2, abhänging von der Umgebung bei der das Plugin eingesetzt werden soll und welche Anforderungen erfüllt werden sollen bzw, wie weit man ältere Versionen unterstützen will.
