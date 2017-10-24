Dokumentation

LibCMVC

PHP Framework

und Library

(Repository: https://github.com/bhohbaum/LibCompactMVC)

<span id="anchor"></span>Vorwort
================================

LibCMVC ist 2010 aus dem Bedarf heraus entstanden, wiederverwendbaren Code von individuellen PHP Applikationen zu sammeln. Dabei wurde primär Wert darauf gelegt, dass das Framework sowohl kompakt und performant ist und es trotzdem erlaubt, schnell und flexibel Webseiten, REST-APIs als auch CLI-Applikationen zu erstellen. Das Framework basiert auf dem MVC Entwurfsmuster und bringt neben den strukturgebenden Elementen (Framework) auch eine Sammlung (Library) von Tools mit, die in der Webprogrammierung oft gebraucht werden. So entstand auch der Name: Lib-Compact-MVC.

Seit seiner Entstehung ist dieses Framework in vielen Webanwendungen, Intranetanwendungen und Mobile-App-Backends auch bei bekannten Unternehmen zum Einsatz gekommen. Es lässt sich hervorragend in einem Continuous Integration Workflow verwenden und von Buildservern testen und installieren.

Durch seinen überschaubaren Umfang ist es besonders performant und Fehler lassen sich gut aufspüren. Der Code selbst enthält Dokumentation im Javadoc-Stil, was das Programmieren in Eclipse z.B. deutlich erleichtert, durch Code-Completion und Dokumentation, die beim Schreiben des Codes erscheint.

Diese Dokumentation soll den Einstieg erleichtern und einen Überblick geben. Fortgeschrittene werden schnell die inneren Zusammenhänge überschauen und auch in der Lage sein, das Framework nach eigenem Bedarf zu erweitern.

<span id="anchor-1"></span>Framework – Die Grundlagen
=====================================================

<span id="anchor-2"></span>Aufbau von URLs
------------------------------------------

Der Aufbau der URLs und wie die Werte an das Framework übergeben werden, ist in der .htaccess Datei im Stammverzeichnis definiert. Zum Betrieb des Frameworks wird mod\_rewrite vorausgesetzt. Ein Beispiel:

http://lcmvc.de/app/action/param0/param1/param2 …

Erklärung der Komponenten:

http://lcmvc.de

Dieser Teil der URL wird in config.php als BASE\_URL definiert.

/app

Gibt an, dass keine Datei aus dem Webroot direkt geladen werden soll, sondern der Request an die Anwendung weitergeleitet werden soll. Dieser Teil der URL kann (und sollte) bei mehrsprachigen Webseiten zur Angabe der Sprache verwendet werden. In diesem Fall steht an dieser Stelle das Sprachkürzel (z.B.: de oder en).

/action

Die Action gibt an, welcher Controller angesteuert wird. Das Mapping zwischen action String und Controller Klassenname wird mit der set\_handler() Methode in index.php dem ActionDispatcher übergeben.

Alle Teile der URL ab 'action', 'param0' (allgemein: alle Elemente des $\_REQUEST Arrays) werden als gleichnamige Variablen in allen Klassen, die von InputSanitizer direkt oder indirekt ableiten, bereitgestellt. Dazu gehören alle von CMVCController ([5](#anchor-3)) (also auch von CMVCComponent ([9](#anchor-4))) abgeleiteten Klassen als auch die Templates ([9](#anchor-5)). Templates werden im Kontext der View Klasse ausgeführt und ihnen stehen somit seine Variablen und Methoden zur Verfügung. Die Funktion kann über das REGISTER\_HTTP\_VARS define in config.php ([16](#anchor-6)) an- und abgeschaltet werden. Ist sie abgeschaltet, kann nur noch über die CMVCController::request() Methode auf die Werte zugegriffen werden. Wird versucht, auf eine Variable zuzugreifen, die durch den Request nicht gesetzt ist, wird eine InvalidMemberException geworfen.

<span id="anchor-7"></span>Anwendungsstruktur und Routing
---------------------------------------------------------

Bei einer LibCMVC Applikation wird das Routing durch die Controller, in Zusammenarbeit mit dem ActionMapper, definiert. Damit der ActionMapper arbeiten kann, muss eine Klasse von ihm abgeleitet werden ([ApplicationMapper](#anchor-8)), in deren Konstruktor alle benötigten Mappings definiert werden. Eine Instanz dieser Klasse wird beim Applikationsstart dem ActionDispatcher (in index.php) im Konstruktor als zweiter Parameter übergeben. Der erste Parameter ist ein String der den Namen der Variable („action“) definiert. Hier sollte der Name der Variable übergeben werden, wie er in den mod\_rewrite-Regeln in .htaccess definiert ist. Es wird allerdings empfohlen, diesen nicht zu ändern.

<span id="anchor-8"></span>ApplicationMapper
--------------------------------------------

Im Konstruktor des ApplicationMappers werden alle URLs definiert, die in der Anwendung benötigt werden. Hier wird auch definiert, welche URLs der Anwendung in der Sitemap erscheinen sollen. Hierzu müssen zwei Arrays entsprechend befüllt werden.

Ein Beispiel:

$this-&gt;mapping2\["app"\]\["home"\] = **new **LinkProperty("/app/start", **true**);

$this-&gt;mapping3\["app"\]\["ajaxep"\]\["user"\] = **new **LinkProperty("/app/ajaxep/benutzer", **false**);

Das mapping2 Array definiert alle Links der ersten Ebene, also solche, die direkt Controller ansteuern. Das mapping3 Array ist für alle dreiteiligen (/1/2/3) URLs zuständig.

Der zweite Parameter des LinkProperty Konstruktors definiert, ob der Link mit in die Sitemap aufgenommen werden soll, oder nicht. Um selbige in einem Controller auszugeben, genügt folgende Zeile:

$this-&gt;binary\_response(ApplicationMapper::*get\_instance*()-&gt;get\_sitemap(), MIME\_TYPE\_HTML);

Dem aufmerksamen Leser wird bereits aufgefallen sein, dass im Beispiel die Schlüssel der Arrays nicht komplett identisch zu den URLs in den LinkProperties sind. Auf diese Weise lassen sich leicht Änderungen an den URLs der Anwendung machen (dies ist oft nötig zum Zwecke der Suchmaschienenoptimierung), ohne dass man alle Stellen umprogrammieren muss, die mit dem Wert arbeiten. Innerhalb der Anwendung wird beim zweiten Beispiel die action Variable im Controller den Wert „ajaxep“ und der param0 den wert „user“ haben, obwohl die Anwendung mit der URL

BASE\_URL . "/app/ajaxep/benutzer"

gestartet wurde. Diese SEO-Optimierungen lassen sich nur auf die beiden URL-Komponenten „action“ und „param0“ anwenden. Das ist üblicherweise vollkommen ausreichend, weil zur SEO-Optimierung zwar sprechende URLs gerne gesehen sind, aber keine sinnlos langen, bzw. tiefe Verschachtlungen. An diesem Punkt wird wieder eine der Maximen dieser Frameworks sichtbar: Code, der nicht unbedingt benötigt wird, bläht das Projekt nur sinnlos auf.

<span id="anchor-3"></span>Controller
-------------------------------------

Controller sind im MVC-Entwurfsmuster die zentralen Bindeglieder zwischen Benutzereingaben, Datenbank ([ORM](#anchor-9)) und Views ([Templating](#anchor-5)). Sie werden in LibCMVC von CMVCController abgeleitet. Es gibt eine ganze Reihe von Methoden, die dafür vorgesehen sind, in Controllern überschrieben zu werden. Sie werden ein einer bestimmten Reihenfolge selektiv aufgerufen, abhängig vom Request-Typ:

Bei HTTP(S) Requests wird nach GET, POST, PUT und DELETE unterschieden, wenn die Anwendung per CLI aufgerufen wurde, werden die EXEC Methoden ausgeführt. Zusätzlich gibt es auch Methoden, die immer aufgerufen werden, egal welche der fünf zuvor genannten Situationen zutrifft.

### <span id="anchor-10"></span>Zu überschreibende Methoden und ihre Ausführungs-Reihenfolge:

**protected function **pre\_run\_get();

**protected function **pre\_run\_post();

**protected function **pre\_run\_put();

**protected function **pre\_run\_delete();

**protected function **pre\_run\_exec();

**protected function **pre\_run();

**protected function **main\_run\_get();

**protected function **main\_run\_post();

**protected function **main\_run\_put();

**protected function **main\_run\_delete();

**protected function **main\_run\_exec();

**protected function **main\_run();

**protected function **post\_run\_get();

**protected function **post\_run\_post();

**protected function **post\_run\_put();

**protected function **post\_run\_delete();

**protected function **post\_run\_exec();

**protected function **post\_run();

Alle \*\_get(), \*\_post(), \*\_put(), \*\_delete() und \*\_exec() Methoden werden nur abhängig von der HTTP Methode des aktuellen Requests ausgeführt. Die Methoden pre\_run(), main\_run() und post\_run() werden immer ausgeführt, in genau dieser Reihenfolge. Ein Beispiel:

Bei einem HTTP-Post-Request werden folgende Methoden ausgeführt:

**protected function **pre\_run\_post();

**protected function **pre\_run();

**protected function **main\_run\_post();

**protected function **main\_run();

**protected function **post\_run\_post();

**protected function **post\_run();

Es ist die Aufgabe des Programmierers, in diesen Methoden die Ein- und Ausgabe zu verarbeiten, die im Request an den Server gesendeten Variablen und deren Inhalte werden vom InputSanitizer als Member-Variablen bereitgestellt.

Die Unterscheidung zwischen GET, POST, PUT und DELETE eignet sich besonders zur Implementierung von REST Endpunkten für CRUD-Operationen. Üblicherweise werden diese aber nicht in Controllern, sondern in Components implementiert.

Dieses Schema ist jedoch nicht immer günstig. Manchmal möchte man mit einer Variable steuern, welche Methoden aufgerufen werden. Als Beispiel legen wir einen Ajax Endpunkt an, der mehrere Funktionen anbietet. Die URLs sollen folgendermaßen aussehen:

*http://libcmvc.de/app/ajaxep/login*

*http://libcmvc.de/app/ajaxep/logout*

http://libcmvc.de/app/ajaxep/createcart

Die drei Funktionen sollen als Methoden direkt im AjaxEP Controller implementiert werden und nicht in Components ausgelagert werden. Dazu schreibt man folgende Zeile in die Methode main\_run():

$this-&gt;dispatch\_method($this-&gt;param0);

In der Variable param0 wird der entsprechende Teil der URL geliefert („login“, „logout“ und „createcart“). Als Beispiel nehmen wir den Fall „login“. Daraus ergeben sich folgende Methodennamen:

**protected function **get\_login();

**protected function **post\_login();

**protected function **put\_login();

**protected function **delete\_login();

**protected function **exec\_login();

**protected function **login();

Hier gilt wieder das gleiche Schema: login() wird immer aufgerufen, die restlichen Methoden nur selektiv beim entsprechenden Request-Typ bzw. exec\_login() beim Aufruf per CLI.

Ergänzend zu dispatch\_method() gibt es noch folgende Methoden um den Aufruf an Components zu verteilen:

$this-&gt;set\_component\_dispatch\_base($this-&gt;param0);

$this-&gt;dispatch\_component(**new **UserComponent());

$this-&gt;dispatch\_component(**new **CartComponent());

$this-&gt;component\_response();

Als erstes muss durch den Aufruf der Methode set\_component\_dispatch\_base() festgelegt werden, auf Basis welcher Variable die Verteilung vorgenommen werden soll. In einem Controller ist das üblicherweise param0. Die nachfolgenden Aufrufe zu dispatch\_component() überprüfen, ob der Inhalt vom param0 der jeweiligen Component ID entspricht. Trifft das zu, wird die Component ausgeführt. Abschließend wird component\_response() aufgerufen um die Ausgabe der ausgeführten Component durchzureichen und deren MIME-Type zu übernehmen.

Dieses Vorgehen bietet sich vor allem bei REST-APIs an um Endpunkte anzusteuern. Man kann Components aber auch dazu verwenden, um Seitenteile zu rendern, die an mehreren Stellen wiederkehren. In dem Fall dürfen sie nicht selektiv ausgeführt werden, sondern müssen immer zusammen mit den Controllern ausgeführt werden, die ihre Inhalte verwenden wollen. Um das zu realisieren, fügt man im entsprechenden Controller alle benötigten Components mit

$this-&gt;add\_component(**new **CartComponent());

hinzu. Die generierte Ausgabe der Components kann in den Templates verwendet werden:

&lt;?= $this-&gt;component("cartcomponent") ?&gt;

Als Parameter muss die Component ID ([9](#anchor-4)) übergeben werden.

Um die Ausgabe in Controllern zu steuern bestehen mehrere Möglichkeiten:

### <span id="anchor-11"></span>Ausgabe via Template

Jedem Controller steht ein eigenes View-Objekt zur Verfügung, auf das über die Methode

$this-&gt;get\_view()

zugegriffen werden kann. Dem View muss mitgeteilt werden, welche Templates es in welcher Reihenfolge zu laden hat und ihm müssen alle Werte übergeben werden, die man im Template zur Ausgabe benötigt. Zusätzlich gibt es noch die Möglichkeit, gewisse Bereiche im Template zu aktivieren, bzw. zu deaktivieren. Folgende Methoden stehen dafür zur Verfügung:

$this-&gt;get\_view()-&gt;add\_template("home.tpl");

$this-&gt;get\_view()-&gt;set\_template(10, "home.tpl");

$this-&gt;get\_view()-&gt;set\_value("users", $users);

$this-&gt;get\_view()-&gt;activate("admin\_pane");

$this-&gt;get\_view()-&gt;deactivate("admin\_pane");

Mit add\_template(name) fügt man Templates hinzu. Sie werden in der gleichen Reihenfolge gerendert wie in der sie hinzugefügt wurden. Die Dateien werden relativ zum Ordner /templates gesucht. Gibt man also nur einen Dateinamen an, wird die Datei in /templates gesucht. Unterordner sind möglich, müssen dann auch mit angegeben werden.

Will man Templates in einer anderen Reihenfolge hinzufügen, als in der sie gerendert werden sollen, muss die set\_template(pos, name) Methode genutzt werden. Ihr erster Parameter gibt die Position an, an der das Template gerendert werden soll. Die Zahlen müssen nicht fortlaufend sein.

Mit set\_value(key, value) kann man beliebige Werte an die Templates übergeben. Im Template kann auf sie mit der get\_value(key) Methode wieder zugegriffen werden.

Der MIME-Type der Response ist standardmäßig MIME\_TYPE\_HTML. Er kann durch den Aufruf der Methode

$this-&gt;set\_mime\_type(MIME\_TYPE\_JPG);

geändert werden. Der MIME-Type aus dem Beispiel macht natürlich in Kombination mit Templating wenig Sinn.

### <span id="anchor-12"></span>Ausgabe von JSON Strings

In REST APIs und AJAX Endpunkten ist die Ausgabe mit Hilfe von Templates nicht sinnvoll. Hierfür sieht LibCMVC die Methode

$this-&gt;json\_response($obj);

vor. Das übergebene Objekt wird in einen JSON-String serialisiert und mit dem MIME-Type MIME\_TYPE\_JSON ausgegeben. Sobald diese Methode in einem Controller aufgerufen wurde, ist die Ausgabe via Templating deaktiviert. Sie kann jedoch mit folgendem Aufruf wieder reaktiviert werden:

$this-&gt;get\_view()-&gt;clear();

Die clear() Methode der Klasse View setzt das gesamte View-Objekt zurück. Alle übergebenen Templates, Werte und Aktivierungen sind danach wieder vergessen.

### <span id="anchor-13"></span>Ausgabe von Binären Daten

Zur Ausgabe von Binären Daten wie z.B. dynamisch berechneten Bildern steht eine weitere Methode zur Verfügung:

$this-&gt;binary\_response($obj);

Sie gibt den Inhalt der ihr übergebenen Variable unverändert aus und setzt den MIME-Type der Response auf MIME\_TYPE\_OCTET\_STREAM. Sollte ein anderer MIME-Type gewünscht sein, kann dieser als zweiter Parameter übergeben werden.

### <span id="anchor-14"></span>Durchreichen des Outputs von Components

Die bereits erwähnte Methode

$this-&gt;component\_response();

sorgt dafür, dass die Ausgabe der zuvor ausgeführten Component ausgegeben und deren MIME-Type durchgereicht wird.

<span id="anchor-4"></span>Components
-------------------------------------

Components unterscheiden sich nicht sehr von Controllern. Alle Components müssen von CMVCComponent ableiten, das seinerseits von CMVCController abgeleitet ist. So bieten sie alle Funktionalitäten von Controllern und bringen noch ein paar Extras mit:

Jede Component muss die abstrakte Methode get\_component\_id() überschreiben und in ihr die Component ID als String zurückgeben.

Desweiteren gibt es noch ein Konstrukt namens base\_param. Dieser ermöglicht es Components zu schreiben und sie einfach auf unterschiedlichen Positionen, was die Pfad-Tiefe der URL anbelangt, zu betreiben. Ein Beispiel:

Eine Component wird 2x im Projekt verwendet, einmal direkt unterhalb eines Controllers und ein weiteres Mal unterhalb einer anderen Component (man kann sie beliebig tief verschachteln!). Damit man beide Male mit dem gleichen Aufruf auf URL-Pfad-Teile zugreifen kann, müssen diese „verschoben“ werden:

http://libcmvc.de/app/ajaxep/user/demo/wert

http://libcmvc.de/app/ajaxep/groups/23/demo/wert

Unsere Beispiel-Component muss auf den Wert „wert“ zugreifen. Dieser befindet sich im ersten Beispiel in der Variable param2 und im zweiten Beispiel in der Variable param3. Die Beispiel-Component greift über die Methode

$this-&gt;param(1);

auf die Variable zu, davon ausgehend, dass param0 für den dispatch mit ihrer eigenen ID im übergeordneten Controller/Component verwendet wurde. Dazu muss der übergeordnete Controller/Component ihr den richtigen base\_param setzen. Im ersten Beispiel müsste der base\_param auf 1, im zweiten auf 2 gesetzt werden. Der Wert des base\_params wird zum übergebenen Parameter der Methode param() addiert und ergibt so den endgültigen Variablennamen, deren Inhalt dann von der Methode param() zurückgeliefert wird.

Der base\_param kann entweder dem Konstruktor der Component übergeben werden oder über die Methode set\_base\_param() gesetzt werden.

<span id="anchor-5"></span>Templating
-------------------------------------

Als Templating-Sprache wird PHP selbst verwendet. Es wurde bewusst keine neue Sprache für das Templating eingeführt, da PHP selbst eine Templating-Sprache ist und der Umweg über eine neue/andere Templating-Sprache zu lasten der Performanz ginge und mehr Lernaufwand für den Programmierer bedeutet. Statt dessen wurde eine Reihe von Best Practices eingeführt:

- Keine mehrzeiligen PHP-Tags in Templates zu verwenden. Sollten doch mehrere Zeilen Templating-Logik aufeinander folgen, wird jede von eigenen PHP-Tags umschlossen.

- Werden Werte ausgegeben, kommt das kurzform-Tag &lt;?= ?&gt; statt echo() zum Einsatz.

- Man sollte sich in Templates auf Schleifen-Logik, bedingte Ausführung/Ausgabe und die Methoden, die die View-Klasse bereit stellt, beschränken. Der Rest ist HTML/Javascript.

Alle Templates werden im Kontext der View-Klasse ausgeführt. Dabei sind folgende drei Methoden von besonderer Bedeutung:

&lt;?= $this-&gt;component("cartcomponent") ?&gt;

&lt;?= $this-&gt;get\_value("key") ?&gt;

&lt;?php **if **($this-&gt;is\_active("part")) { ?&gt;

&lt;div&gt;Beispiel&lt;/div&gt;

&lt;?php } ?&gt;

Die Bedeutung der component() Methode wurde bereits im Kapitel [Controller](#anchor-3) erleutert, ebenso wie die Methode get\_value() im Kapitel [Ausgabe via Template](#anchor-11). is\_active() ist das Gegenstück zu activate(), was ebenfalls bereits im Kapitel [Ausgabe via Template](#anchor-11) erwähnt wurde. In obigem Beispiel wird das DIV mit dem Inhalt „Beispiel“ nur dann ausgegeben, wenn im zugehörigen Controller oder Component folgender Aufruf gemacht wurde:

$this-&gt;get\_view()-&gt;activate("part");

Man sollte unbedingt darauf achten, dass alle Werte vom Controller an das View übergeben werden und nie aus Templates heraus Daten nachladen, die nicht vom Controller übergeben wurden. Andernfalls kann es zu problemen beim Caching kommen, da es mögliche Veränderungen nicht bemerkt. Das Caching serialisiert das View Objekt und bildet einen Hash darüber um bereits gerenderte Objekte im Cache zu lokalisieren.

<span id="anchor-15"></span>Linkerzeugung
-----------------------------------------

Zur Linkerzeugung steht die globale funktion lnk() zur Verfügung, in Views kann man auch

$this-&gt;lnk()

verwenden. Beide Funktionen haben folgende Vier optionalen Parameter:

**function **lnk($action = **null**, $param0 = **null**, $urltail = "", $lang = **null**);

Die beiden Variablen $action und $param0 wählen intern die entsprechende LinkProperty aus einem der beiden Array aus, die man im ApplicationMapper definiert hat. Muss eine längere URL generiert werden als die jeweilige LinkProperty liefert, übergibt man den String in $urltail. Die Variable $lang wird benötigt, wenn die Anwendung mehrsprachig implementiert ist und das „/app“ am Anfang des Pfades der URL durch Landeskürzel ersetzt wurde. In dem Fall wird diese Variable zur Auswahl der ersten Array-Dimension der mapping-Arrays verwendet.

<span id="anchor-16"></span>Umleitungen
---------------------------------------

Umleitungen werden in LibCMVC mit RedirectException(s) realisiert. Es gibt zwei verschiedene Typen von Umleitungen: interne und externe. Dazu können dem Konstruktor der RedirectException bis zu drei Parameter übergeben werden, in denen 1. das Ziel (die action für interne, eine URL für externe Redirects), 2. der HTTP-Code (für externe Redirects standardmäßig 302, für interne ohne Funktion) und 3. ein Bool Wert (true für interne, false für externe Weiterleitungen), angegeben werden.

### <span id="anchor-17"></span>Interne Umleitungen

Interne Umleitungen übergeben die Kontrolle an einen anderen Controller. Da jeder Controller über sein eigenes View-Objekt verfügt, wird alle bis zu dem Zeitpunkt bereits an das View übergebene Information verworfen, so dass der neue Controller an den umgeleitet wurde, sauber starten kann.

Diese Art der Umleitung bietet den Vorteil, dass der Browser des Nutzers keine erneute Anfrage an die Anwendung schicken muss, der Client bekommt von der Umleitung nichts mit. Der Nachteil besteht darin, dass der neue Controller mit den Request-Daten des alten Controllers umgehen muss, dafür aber eigentlich nicht geschaffen wurde.

### <span id="anchor-18"></span>Externe Umleitungen

Externe Umleitungen senden standardmäßig eine 302-Response (Temporary Redirect) an den Client und fordern ihn so dazu auf einen erneuten Request an die Anwendung zu senden, durch den dann der Ziel-Controller aufgerufen wird.

<span id="anchor-19"></span>Zugriffsberechtigungen
--------------------------------------------------

Standardmäßig wählt die action Variable aus, welcher Controller angesteuert wird. Man kann jedoch einen zusätzlichen Controller definieren, der bei jedem Request ausgeführt wird, bevor zu dem Controller gesprungen wird, der durch den Request angefordert wurde. Dieser Controller muss in index.php mit dem Aufruf der ActionDispatcher-Methode set\_control() festgelegt werden.

Dieser Controller hat dann die Möglichkeit bei jedem Request zu validieren, ob der jeweilige Client die Berechtigung besitzt auf die Teile der Anwendung zuzugreifen, die im Request angegeben sind. Sollte das nicht der Fall sein, kann mit einer RedirectException korrigierend eingegriffen werden und der Zugriff so blockiert werden.

<span id="anchor-9"></span>ORM
------------------------------

Bei ORMs gibt es zwei Herangehensweisen, wie die Zusammenarbeit mit der Datenbank bzw. ihre Erstellung von statten geht. Bei Laravel z.B. wird die Struktur der Datenbank vom ORM verwaltet. LibCMVC geht den anderen Weg, wie er z.B auch vom YII Framework beschritten wird: Das ORM „lernt“ von der Datenbank selber die Struktur. Beide Herangehensweisen haben ihre Vor- und Nachteile, letztere spielt aber besser mit der Anforderung zusammen, mit so wenig Code wie möglich zum Ziel zu kommen.

Es wird empfohlen, Die Datenbankstruktur in der MySQL Workbench oder einem vergleichbaren Programm zu erstellen. Die Workbench bietet den voreilt, dass man das Datenbankschema grafisch erstellen kann und mit wenigen Klicks die Änderungen auch in bestehende, bereits mit Daten gefüllte Datenbanken, einbringen kann. So hat man Migrationen von unterschiedlichen Datenbankschema-Versionen unter manueller Kontrolle und den Vorteil, dass man immer eine übersichtliche Grafik des Datenbankschemas beim programmieren zur Hand hat.

Gerade Einsteigern kommt diese Herangehensweise zu gute, da sie nicht erst lernen müssen, wie sie die Datenbank auf dem Umweg über das Framework unter ihre Kontrolle bringen. Statt dessen wird die Datenbank mit ihrer eigenen IDE entworfen und die Anwendung mit der PHP-IDE der Wahl.

Um sich in den Controllern und Components zusätzliche Methodenaufrufe zu sparen, die festlegen, auf welcher Tabelle ein DbObject arbeiten soll, wird empfohlen, zu jeder Tabelle bzw. View der Datenbank eine PHP-Klasse gleichen Namens zu erstellen. Existiert z.B. eine Tabelle namens „user“, legt man einfach eine leere Klasse gleichen namens in /application/dba an:

&lt;?php

**if **(file\_exists('../../include/libcompactmvc.php'))

**include\_once **('../../include/libcompactmvc.php');

LIBCOMPACTMVC\_ENTRY;

/\*\*

 \* user.php

 \*

 \* **@author** Botho Hohbaum &lt;bhohbaum@googlemail.com&gt;

 \* **@package** Siemens CMS

 \* **@copyright** Copyright (c) Media Impression Unit 08

 \* **@license** BSD License (see LICENSE file in root directory)

 \* **@link** http://www.miu08.de

 \*/

**class **user **extends **DbObject {

}

In den DTO Klassen stehen drei Methoden zur Verfügung, die dafür vorgesehen sind, überschrieben zu werden:

init()

Diese Methode wird vom parent Constructor aufgerufen und dient der Initialisierung des Objektes. Soll das Objekt auf einer Tabelle arbeiten, die anders als die Klasse bezeichnet ist, sollte diese Methode überschrieben werden und darin mit einem Aufruf von $this-&gt;table() der abweichende Tabellenname gesetzt werden. Davon wird allerdings dringend abgeraten. Sollten andere Initialisierungen benötigt werden, können sie ebenso in der init() Methode vorgenommen werden, dabei muss aber darauf geachtet werden, dass die parent-Methode aufgerufen wird oder ein manueller Aufruf von $this-&gt;table() den zu verwendenden Tabellennamen festlegt. Generell sollte man sich angewöhnen, überschriebene Methoden aufzurufen, siehe [Funktionsheader](#anchor-20).

on\_after\_load()

Wird aufgerufen, nachdem der entsprechende Datensatz aus der Datenbank in das Objekt geladen wurde. Hier kann zusätzliche Logik untergebracht werden, z.B. wenn gewisse Felder abhängig von Informationen aus anderen Tabellen besondere Inhalte erhalten sollen. Dadurch lassen sich z.B. Werte überschreiben oder auch zusätzliche Felder anlegen, die in der zugrunde liegenden Tabelle gar nicht existieren.

on\_before\_save()

Ist das Gegenstück zu on\_after\_load(): Sie wird als erstes ausgeführt wenn auf einem DbObject die save() Methode ausgeführt wird, bevor der Inhalt des Objekes in die Datenbank gespeichert wird. Dies wird z.B. benötigt, um künstliche Werte, die durch Logik in der on\_after\_load() Methode eingeblendet wurden, wieder auf die ihnen zugehörigen Tabellen zu verteilen.

### <span id="anchor-21"></span>Datenbankschema

WICHTIG!!!

Damit das ORM Datensätze eindeutig identifizieren kann, ist es wichtig, dass in der Datenbank keine zusammengesetzten Primärschlüssel existieren und jede Tabelle muss einen Primärschlüssel haben. Dies gilt vor allem beim Erstellen von Verbindungstabellen bei n:m Beziehungen. Diese erhalten per default einen zusammengesetzten Primärschlüssel aus den beiden Fremdschlüsseln. Diesen Tabellen muss ein autoincrement-Feld als Primärschlüssel hinzugefügt werden, der UNIQUE-Index auf den beiden anderen Spalten sollte natürlich bestehen bleiben.

### <span id="anchor-22"></span>Arbeiten im Controller mit dem ORM

Im Controller unterscheiden sich zwei grundlegende Formen des Zugriffes auf das ORM. Sie unterscheiden sich durch die Antwort auf die Frage: Wird ein einzelner Datensatz gebraucht, oder mehrere (ein Array)?

Um einen einzelnen Datensatz aus der Datenbank zu laden, instanziiert man ein Objekt der entsprechenden Klasse und übergibt der by() Methode ein Array mit den benötigten Constraints:

$user = **new **user();

$user-&gt;by(**array**(

"name" =&gt; $this-&gt;user

));

In obigem Beispiel wird ein user Datensatz geladen, dessen Feld „name“ gleich dem Inhalt der variable „user“ ist, welche im Request gesendet wurde. Alle Elemente des Constraint Arrays werden mit AND verknüpft.

Es ist darauf zu achten, dass durch das Constraint auch wirklich nur ein Datensatz geladen wird, andernfalls wird eine MultipeResultsException geworfen.

Wenn man mehrere Datensätze aus einer Tabelle laden muss, muss man die all() oder die all\_by() Methode von DbAccess benutzen. Da DbObject von DbAccess abgeleitet ist, stehen diese Methoden auch allen DTOs zur verfügung. Folgender Aufruf lädt alle Datensätze aus der user Tabelle:

$users = (**new **user())-&gt;all();

Als Ergebnis herhält man ein Array aus user-Objekten. Auch hier gibt es die möglichkeit die Ergebnismenge zu beschränken, analog zur by() Methode steht hier all\_by() zur verfügung:

$users = (**new **user())-&gt;all\_by(**array**("name" =&gt; $this-&gt;param1));

Mit diesem Aufruf kann man alle Benutzer gleichen namens aus der Tabelle laden. Das Ergebnis ist wieder ein Array aus user-Objekten.

### <span id="anchor-23"></span>Automatische Fremdschlüssel-Auflösung

Fremdschlüsselfelder werden beim lesenden Zugriff automatisch zum referenzierten Datensatz aufgelöst. Angenommen die user-Tabelle hat einen Fremdschlüssel namens „type\_id“ welcher auf eine Tabelle namens „type“ verweist und man hat das user-Objekt bereits geladen, dann kann man direkt auf die Felder des type Objektes zugreifen (in diesem Beispiel auf das Feld „type.name“):

$user-&gt;type\_id-&gt;name

Im schreibenden Zugriff enthält type\_id die id, so wie sie in dem user-Datensatz in der Datenbank steht. Möchte man also den user-Datensatz modifizieren, dass er auf einen anderen type-Datensatz zeigt, muss dessen id zugewiesen werden:

$user-&gt;type\_id = 19; // neue id

Manchmal ist diese automatische Fremdschlüssel-Auflösung nicht gewünscht. Sie kann für jedes DTO einzeln an- und abgeschaltet werden. Hierzu gibt es zwei Methoden:

fk\_resolution(bool $enabled)

Jedes DTO stellt diese Methode public bereit. Sie schaltet für das jeweilige DTO die Auflösung an bzw. aus.

fk\_resolution\_enabled()

Mit dieser Methode kann man den aktuellen Status der Fremdschlüsselauflösung abfragen.

Manchmal ergeben sich Verkettungen über mehrere Tabellen hinweg und man möchte z.B. auf 2. Ebene die Fremdschlüsselauflösung zur user-Tabelle unterbinden, da man den Passwort-Hash nicht veröffentlichen möchte.

Beispiel: Eine Tabelle „test“ referenziert eine Tabelle „file“ und selbige referenziert eine Tabelle „user“. Man möchte, dass alle Daten aus test und alle Daten aus file serialisiert werden, aber keine Daten aus user. Dann muss im file-DTO die Fremdschlüsselaulösung deaktiviert werden:

$test-&gt;file\_id-&gt;fk\_resolution(**false**);

Danach kann $test serialisiert werden, ohne dass der zugehörige user Datensatz mit enthalten ist. Das Feld $test-&gt;user\_id wird dann nur noch die id, aber nicht den ganzen Datensatz enthalten.

### <span id="anchor-24"></span>Datensätze speichern und löschen

Jeder von DbObject abgeleitetes Objekt hat die beiden Methoden save() und delete() geerbt. Mit ihnen können am Objekt vorgenommene Änderungen wieder in die Datenbank geschrieben, beziehungsweise der ganze Datensatz gelöscht werden.

### <span id="anchor-25"></span>Komplexere Custom-Queries

Die bisher erwähnten Möglichkeiten mit der Datenbank zu arbeiten sind zwar recht minimalistisch, reichen aber für über 90% aller Anwendungsfälle. Manchmal braucht man es aber ein wenig komplexer. Für diesen Fall kann man entweder ein View erstellen, oder die Anwendung mit einer custom Query versehen. Die folgende Beispielfunktion würde man in der DBA\_DEFAULT\_CLASS implementieren:

**public function **get\_user\_by\_name($name, $obj = **false**) {

DLOG();

$q = "SELECT\*

FROMuser

WHEREname LIKE '" . $this-&gt;escape($name) . "'";

**return **$this-&gt;run\_query($q, **true**, $obj, **null**, TBL\_USER);

}

$this-&gt;escape() sollte beim Zusammenbauen des Query-Strings unbedingt zum Escapen von Benutzer-kontrolliertem Inhalt verwendet werden um SQL-Injections zu verhindern.

$this-&gt;run\_query() führt die Query aus und liefert das Ergebnis zurück. Die Parameter der Funktion sind in DbAccess genau erklärt.

In den Controllern erhält man Zugriff auf die DBA\_DEFAULT\_CLASS und ihre Methoden über $this-&gt;get\_db(). Wird der Zugriff von anderen Klassen aus benötigt, kann man auf das Objekt mit folgendem Aufruf zugreifen:

DbAccess::get\_instance(DBA\_DEFAULT\_CLASS)

DBA\_DEFAULT\_CLASS muss in config.php als String definiert werden, der den Klassennamen der Standard-Datenbankzugriffs-Klasse wiedergibt. Üblicherweise liegt diese in /application/dba/dba.php und heißt DBA.

<span id="anchor-26"></span>Codekonventionen
--------------------------------------------

### <span id="anchor-27"></span>Dateiheader und -footer

Jede Datei der Anwendung sollte folgenden Header haben:

&lt;?php

**if **(file\_exists('../../include/libcompactmvc.php'))

**include\_once **('../../include/libcompactmvc.php');

LIBCOMPACTMVC\_ENTRY;

Dabei ist darauf zu achten, dass der Pfad zur libcompactmvc.php angepasst werden muss, sollte sich die Datei auf einer anderen Verzeichnisebene befinden.

Dieser Header sorgt dafür, dass die Datei nicht direkt aufgerufen werden kann. Statt dessen wird folgende Meldung ausgegeben:

Invalid entry point

Für den Footer jeder Datei wird empfohlen, vom schließenden PHP-Tag

?&gt;

abzusehen. Es kann sonst sehr leicht passieren, dass versehentlich ein Whitespace dem schließenden Tag folgt. Dieses wird an jeden Output angehängt und kann so vor allem bei binärem Output zu Problemen führen.

### <span id="anchor-20"></span>Funktionsheader

Zu Logging- und Debug-Zwecken wird empfohlen, bei allen neuen Methoden als erste Zeile ein

DLOG();

einzufügen. In der zweiten Zeile darunter sollte zunächst die überschriebene parent-Methode aufgerufen werden. Manchmal, in sehr seltenen Fällen, ist es sinnvoll die parent-Methode nur optional auszuführen, in den meisten Fällen wird jedoch empfohlen dies einfach in allen Fällen gleich zu implementieren. Übergeordnete Klassen verlassen sich normalerweise darauf, dass die Methoden, die sie implementieren, auch aufgerufen werden.

<span id="anchor-28"></span>CLI
-------------------------------

Alle Funktionen einer mit LibCMVC erstellten Anwendung lassen sich auch über die Kommandozeile ansteuern. Neben den exec\_\* Methoden, die standardmäßig in Controllern/Components im CLI-Modus aufgerufen werden, können auch alle anderen Funktionen der Anwendung über das Cli aufgerufen werden. Hierzu muss die Umgebungsvariable METHOD auf den zu emulierenden Request-Typ gesetzt werden. Ein Beispiel:

PHPSESSID=hhtntia6spfmdv79f2iob1ddk3 METHOD=get php index.php home

Obiger Befehl lässt die Anwendung die gleiche Ausgabe zu STDOUT ausgeben, wie ein GET-Request auf die URL:

*B*ASE\_URL . "/app/home"

mit dem zugehörigen Cookie für die selbe Session ID. Um weitere Variablen zu übergeben, kann man sie entsprechend im Environment setzen. Sie werden wie Query-String oder Post-Body Variablen den Controllern und Components übergeben. Der Pfad-Teile der URL müssen als Parameter übergeben werden. Dabei wird der erste Parameter als die action-Variable übergeben, der zweite als param0, der dritte als param1 usw...

Ein Beispiel für einen über CLI simulierten AJAX-Request zum einloggen von Benutzern:

user=test pass=password PHPSESSID=hhtntia6spfmdv79f2iob1ddk3 METHOD=post php index.php ajaxep login

… entspricht dem HTTP-Request:

POST auf (Domain beliebig gewählt, kein Unterschied zu HTTPS):

*http://libcmvc.de/app/ajaxep/login*

… mit diesem String im POST-Body:

user=test&pass=password

… und passendem Cookie.

### <span id="anchor-29"></span>Anwendung in eine ausführbare Datei packen

Im Ordner /assets/scripts liegt die Datei compile-to-one-file. Es ist ein Perl-Skript, mit dem man die ganze erstellte Anwendung in eine ausführbare Datei packen kann. Um diese auszuführen wird ein Perl- und ein PHP-Interpreter gebraucht.

Dieses Vorgehen bietet sich an, wenn man keine Webanwendung erstellt hat, sondern eine CLI-Applikation. Für CLI-Applikationen ist es unpraktisch, wenn man immer erst in ein bestimmtes Verzeichnis wechseln muss um von dort aus Befehle ausführen zu können. Die Tatsache, dass man es mit einer größeren Sammlung von Dateien zu tun hat, führt nicht zur Verbesserung der Situation. An diesem Punkt hilft compile-to-one-file weiter. Man muss sich nur noch um eine Datei kümmern und diese kann von überall aus aufgerufen werden. Das Aufrufschema ist unverändert, die eine Datei verhält sich wie die erstellte Anwendung bezüglich Parametern und Umgebungsvariablen.

<span id="anchor-6"></span>config.php
-------------------------------------

Um die Installation der Webanwendung auf mehreren Hosts zu erleichtern, werden alle Konfigurationsoptionen in einer Datei verwaltet. Je nach Bedarf und persönlicher Vorliebe kann man hier entweder für jeden Host eine eigene Datei anlegen und bei der Installation entsprechend umbenennen, oder man verwaltet alle Konfigurationen in einer Datei und unterscheidet mit Hilfe der Funktion gethostname(), welche Optionen auf dem jeweiligen Host verwendet werden sollen. Die einzelnen Optionen sind in config.php selbst dokumentiert.

Generell ist zu empfehlen, dass man beim programmieren einer Anwendung alle „Hart codierten“ Werte in config.php definiert. Dieses Vorgehen erleichtert das spätere Ändern dieser werte, als auch das Entwickeln im Team. In jedem Fall sollten aber Werte, die sich von Installation zu Installation ändern können, dort definiert werden.

<span id="anchor-30"></span>Library Komponenten
===============================================

<span id="anchor-31"></span>ApplePushNotification
-------------------------------------------------

Zum versenden von Apple-Push-Notifications.

<span id="anchor-32"></span>CachedHttpRequest
---------------------------------------------

Ein cachender HTTP Client. Als Cache wird Redis verwendet.

<span id="anchor-33"></span>Captcha
-----------------------------------

Klasse zum einfachen erstellen von einfachen Captchas.

<span id="anchor-34"></span>CenterMap
-------------------------------------

Dieser Klasse kann man eine Reihe von Map-Marker-Positionen übergeben und kriegt als Ergebnis den Mittelpunkt der Karte und die benötigte Zoomstufe, um alle Marker mit GoogleMaps darstellen zu können.

<span id="anchor-35"></span>CephAdapter
---------------------------------------

Eine Klasse zum arbeiten mit einem Ceph SAN. Siehe <http://docs.ceph.com/docs/master/radosgw>.

Der CephAdapter benötigt die Rados PHP-Extension. Er kann benutzt werden wie jede herkömmliche Cloud-Object-Storage.

<span id="anchor-36"></span>MySQL zu SQLite Konverter
-----------------------------------------------------

Im Ordner /assets/scripts liegt ein Bash-Skript zum Konvertieren von MySQL-Datenbanken zu SQLite. Dieser eignet sich besonders wenn das Framework für das Backend einer mobilen App eingesetzt wird, um die App offline-fähig zu machen.

<span id="anchor-37"></span>FIFOBuffer
--------------------------------------

Ein Redis-basierter FIFO Puffer.

<span id="anchor-38"></span>GoogleJWT
-------------------------------------

Eine Google-spezifische JSON-Web-Token Implementierung, zur Authentifizierung an den Google-APIs. Das zurückgegebene Token kann in darauf folgenden Anfragen an die Google APIs zur authentifizierung verwendet werden.

<span id="anchor-39"></span>HTMLMail
------------------------------------

Komponente zum versenden von Text- und/oder HTML-Mails.

<span id="anchor-40"></span>MapRadius
-------------------------------------

Berechnet die Entfernung zwischen zwei punkten auf dem Globus. Die Punkte werden mit Längen- und Breitengraden angegeben. Die Entfernung kann in Kilometern, Meilen und nautischen Meilen ausgegeben werden.

<span id="anchor-41"></span>MapCluster
--------------------------------------

Berechnet Clusterbildung von Markern auf einer Karte. MapMarker und MapClusterMarker gehören mit zu dieser funktion.

<span id="anchor-42"></span>MultiExtender
-----------------------------------------

Diese Klasse ermöglicht Mehrfachvererbung in PHP. Von ihrer Benutzung ist eher abzuraten.

<span id="anchor-43"></span>Mutex
---------------------------------

Eine Redis-basierte Mutex-Implementierung.

<span id="anchor-44"></span>simple\_html\_dom
---------------------------------------------

Ein einfacher HTML DOM Parser/Generator.

<span id="anchor-45"></span>Singleton
-------------------------------------

Abstrakte Klasse zum implementieren von Singletons. Um einen konkreten Singleton zu implementieren, muss eine Klasse von Singleton ableiten.

<span id="anchor-46"></span>SMTP
--------------------------------

Eine einfache SMTP implementierung. Wird von HTMLMail verwendet.

<span id="anchor-47"></span>Socket
----------------------------------

Eine TCP-Socket implementierung. Wird von SMTP verwendet.

<span id="anchor-48"></span>Upload
----------------------------------

Eine Klasse zum einfachen Umgang mit Datei-Upoads.

<span id="anchor-49"></span>UTF8
--------------------------------

Diese Klasse bietet die gleiche funktionalität wie die beiden PHP-Funktionen uf8\_encode() und utf8\_decode(). Es wird aber zusätzlich überprüft, ob bereits UTF8 vorliegt, oder nicht. Somit ist kein doppeltes encoden/decoden möglich, wie die standard PHP-Funktionen es machen würden.

<span id="anchor-50"></span>UUID
--------------------------------

Ein UUID Generator/Validator. Es können UUIDs der Versionen 2, 3 und 4 erstellt werden.

<span id="anchor-51"></span>Validator
-------------------------------------

Eine Klasse zum validieren von Strings.

<span id="anchor-52"></span>WSAdapter
-------------------------------------

Eine Klasse zur Kommunikation mit dem zugehörigen Websocket Server (<https://github.com/bhohbaum/lcmvc-websocket>). Dieser kann verwendet werden, um bidirektionale Kommunikation zwischen Server und Client zu ermöglichen. Das Gegenstück hierzu bildet $ws (in /assets/js/ajax.js).

<span id="anchor-53"></span>XmlToJson
-------------------------------------

Ein einacher XML → JSON Konverter, basiert auf simple\_html\_dom.
