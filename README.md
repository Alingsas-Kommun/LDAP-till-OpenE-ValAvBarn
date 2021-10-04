# API01

Dokumentet beskriver hur man kan bygga en enkel server som tillhanda håller REST-api, JSON/XML-listor samt skapar exportfiler. Tanken är från början att ta ut information från katalogtjänsten för att tillhandahålla den till Open E.

## Installation

Servern är en Linuxserver, Ubuntu LTS. Detta dokument har enbart exempel från Ubuntu. Vilken linux eller windowsversion som helst fungerar.

Installerat på den är Apache samt php. Php har tillägget php-ldap för att kunna prata med LDAP. Php-mbstring är också installerat för kraftfullare sökningar.

Installera Ubuntu enligt den stegvisa guiden. För att följa den behöver du ha en fast ip-adress samt ett namn på servern i DNS. I mitt fall heter servern api01.kommun.se och är det namn jag använder fortlöpande i dokumentet.

När Ubuntu är installerat kör följande:

- sudo apt-get upgrade
- sudo apt-get update
- sudo apt-get install apache2
- sudo apt-get install php
- sudo apt-get install php-ldap
- sudo apt-get install php-mbstring

Efter detta starta om server med sudo reboot.

Du kommer att kunna surfa in på http://api01.kommun.se/ och se en testsida från Ubuntu.

Alla filer ligger i mappen /var/www/html på servern.

Nästa steg är att installera ett certifikat på servern så att https fungerar. Port 80 och http är osäkert och bör stängas av.

Certifikat installeras i samarbete med IT och den leverantör ni har av certifikat. Jag rekommenderar ett vanligt SSL-certifikat och inte Let&#39;s encrypt då server kommer att låsas för internetåtkomst.

OBS! Open E använder java för att hämta data, det innebär att du även måste inkludera ca-bundle så att servern har hela certifikatkedjan. I en vanlig browser så ser man inte detta fel då den läser root-certifikat centralt.

Även LDAP använder certifikat. Viktigt är att php inte längre använder sig av ldaps utan enbart ldap på port 389

Antingen så importerar du certifikaten till servern alternativt så ställer du in så den inte kontrollerar LDAP-certifikatet.

Ändra /etc/ldap/ldap.conf och lägg till:

TLS\_REQCERT never

Vill man ha en bra debug i /log/var/apache2/error.log kan man överst i sitt script lägga till:

ldap\_set\_option(NULL, LDAP\_OPT\_DEBUG\_LEVEL, 7);

## Säkerhet

Så här kan man sätt upp säkerheten kring servern

![](https://it.alingsas.se/wp-content/uploads/2021/10/serversetup.png)

Servern har lokal brandvägg installerad samtidigt som kommunens större brandvägg skyddar.

Man öppnar enbart för Nordic Peak och Open E från utsidan.

Insidan så har man en krypterad koppling mot LDAP-källan, katalogtjänsten.

Enbart de datorer som ska administrera servern har access till den via brandväggsregler. Många IT-avdelningar har eget nät med högre säkerhet.

Port 443 = https, krypterad http

Port 22 = terminalporten ssh, via denna kan man även skicka filer med exempelvis Filezilla

Port 636 = ldaps, krypterad ldap

## Lokal brandvägg

Ufw används som lokal brandvägg. Först låser vi allt.

Sudo ufw default deny incoming

Sedan tillåter vi utgående trafik

Sudo ufw default allow outgoing

Inkommande ssh-trafik (port 22) är tillåten

Sudo ufw allow ssh

Inkommande trafik på port 443 är tillåten

Sudo ufw allow 443

Sist aktiveras lokal brandvägg.

Sudo ufw enable

## Exempelkod för olika scenarior

### Val av barnfrågan

Mål: Att koppla Val av barn -frågan till Open E

Vi vill skicka in personnummer och få tillbaka Skola och Klass

Se koden index.php

### Resultatet från koden är

![](RackMultipart20211004-4-1grswkl_html_8e3063aca4839ced.gif)

I Open E ställer man i val av barnfrågan så här

Viola, nu är Skola och klass med i svaret i Open E.
