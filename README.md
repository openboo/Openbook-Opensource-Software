# Openbook-Opensource-Software
An Anonymous hive mind social media network website. Written in LAMP.
Openbook opensource software was created and tested on a LAMP server:
Server version:		Apache 2.4.18 (Debian Linux)
PHP version:		7.0.6-1
MySQL version:		14.14 Distrib 5.6.3




Created in the spirit of #opRebuildTheHive, #opNewBlood, and...












                               # #
                               # #
                               ###
                               ###
                             #######
                           ##   #   ##
                          #           #
                   #######             #######
                      ###### #opELE  ######
                   #######             #######
                          #           #
                           ##   #   ##
                             #######
                               ###
                               ###
                               # #
                               # #









Welcome to...

                          ___      ,---,
                        ,--.'|_  ,--.' |
                        |  | :,' |  |  :
                        :  : ' : :  :  :
                      .;__,'  /  :  |  |,--.   ,---. 
          ___   ___   |  |   |   |  :  '   |  /     \ 
      ___/  /__/  /__ :__,'| :   |  |   /' : /    /  |
     /___   ___   __/   '  : |__ '  :  | | |.    ' / |
     ___/  /__/  /__    |  | '.'||  |  ' | :'   ;   /|
    /__   ___   ___/    ;  :    ;|  :  :_:,''   |  / |
      /__/  /__/        |  ,   / |  | ,'    |   :    |
                   ,-..  ---`-'  `--''  __   \   \  /     ,--, 
      ,--.--.     / /   \             ,-.'|_  `----'    ,--.'|            
      \   /  \   / .     :   __  ,-.  | | :,'           |  | :            
      |  :    | . /  .;.  \,' ,'/ /|  : : ' :           :  : '    .-.--.  
      |  | .\ :. ;  ; //  ;'  | |' |.;_,'  /    ,-.-.   |  ' |   / /    ' 
      .  : |: |; |  ;// ; ||  |   ,'| |   |    /      \ '  | |  | :  /`./ 
      |  |  \ :| :  ,/ /| ''  :  /  :_,'| :   .--.  .. ||  | :  | :  ;_   
      |  : .  |. |    /|| :|  | '     ' : |__  \__\/:. .'  : |__ \ \   `.  
      :    |`-'' ;   ///  |;  : |     | | '.'| ," .-.; ||  | '.'| `---.  \ 
      :  : :    \ \  ',  / |  , ;     ; :    ;/  / ,.  |;  :    ; / /`-' / 
      |  | :     ; :    /   ---'      | ,   /;  :  .'   \  ,   /' -'.   / 
      `--'.|      \ \ .'               --`-' |  ,    .-./---`-'   `-'--'  
        `--`       `-`                        `--`--'                     
             Created and maintained by Anonymous, for Anonymous.


#thep0rtals are a decentralized, loosely-knit network of websites
powered by Openbook opensource software. Which is basically a simple
rip-off of Twitter, using hashtags to categorize information, but doing
away with individual identities so that we may all speak as One.

It was also made to be easily used in the TOR browser, even if NoScript
is turned on, so that everyone may speak freely and easily. Anonymously.

This social network is held together by announcing the opening of any
new p0rtals using the hashtag #keepthep0rtalsopen. So even if p0rtals
are hosted on free hosting services that run out of bandwidth, or
servers in someone's basement that might get turned off randomly by
their parents, it's still just as good as any other p0rtal. Because if
any p0rtal doesn't work anymore, people can always look up newer p0rtals
using the #keepthep0rtalsopen hashtag. This way, the nameless spirit of
humanity will never be silenced, because there will always be yet
another p0rtal for it/Us to channel through!

We also thought, since this network is so janky, and so loosely held
together, it feels more accessible than some more hardcore online
Anonymous gatherings. All you need to participate is a Tor browser, so
everyone is welcome to take part. And all you need to run Openbook is a
free hosting service that runs PHP and MySQL, so almost anyone can open
a p0rtal to help #keepthep0rtalsopen if they want to. All of this makes
participation seem more fun and light-hearted, inviting anyone/everyone
to take part. All in the spirit of #opNewBlood and #opRebuildTheHive.

For those of you who are not just setting up the software, but also
editing it, changing how it looks, or changing how it WORKS, we hope
you'll find the code quite self-explanatory, and well-commented. Just be
sure to read IMPORTANT.txt before you edit it too much. If you plan on
announcing your p0rtal with #keepthep0rtalsopen and giving up control of
your p0rtal to allow it to be One with the chaos that is #thep0rtals
Anonymous social network, then you must read IMPORTANT.txt first or you
might end up editing it so much it isn't quite a real "p0rtal" anymore..

So, without further ado...



SETTING UP OPENBOOK

This README only covers how to set up the software. However, there is
WAY more important things you need to understand BEFORE opening a
p0rtal. So, BEFORE YOU CONTINUE, PLEASE read IMPORTANT.txt.

If you're using a hosting service, even if it's a free one, they
usually have PHP, MySQL, and probably even phpMyAdmin installed already.
In which case, some of these steps may be redundant.

1. Read IMPORTANT.txt or your website might go to shit real fast, and
   you won't understand what went wrong. (Or what didn't go wrong).
   You'll understand once you read IMPORTANT.txt!
2. Download, install, and set up Apache2. (RTFM)
3. Download, install, and set up PHP. (RTFM)
4. Download and install MySQL. (RTFM)
5. We recommend downloading and installing phpMyAdmin (RTFM) to make
   working with the MySQL database easier. Otherwise you may need to use
   the command line to run MySQL (RTFM).
6. Create a database, name it whatever you want, and remember the
   database name and password. You'll need to write them into the PHP.
7. The file "openbooktables.sql" has the SQL statements necessary to
   CREATE the table structure in your database. If you're using
   phpMyAdmin, select the database you created, and select "RUN" to run
   code on that database. It has the option of uploading the file or
   pasting the code from the file right into it - either will work.
8. Go into the databasesetup.php file, and enter the database name and
   password where it says "databasename" and "databasepassword". These
   will be found once in just about every PHP file. You'll need to edit
   this file again in the next step, so don't close it, yet.
9. Then replace "SQLuser" and "serveraddress" according to the settings
   of your hosting service. If you're hosting it on your own computer,
   the serveraddress will likely be "localhost". It may be "localhost"
   on a hosting service, as well, but you'll have to find out from them.
   They often have it listed somewhere, like on the main page of the
   cpanel, or wherever the other SQL settings are consolidated. The
   "SQLuser" may be a name you picked when you set up SQL, or it may
   have already been picked out for you by your hosting service, based
   on your username or something.
10. Upload all the files! Be sure you keep all the files in the same
    folders they came in, or everything will break.

Hopefully, if your database settings are all correct, it will "just
work"! ;)

Some tips:
- If something is not working, but you can't tell what is wrong, you
   may have to turn on "DEBUG" mode for PHP. It doesn't necessarily show
   all the errors by default, for security purposes.
- Also, if you're just lost in the dark at this point, try putting
   "echo phpinfo();" at the top of a PHP file to see if PHP is
   even working, and see what you can figure out from there. This
   command typically shows a huge list of PHP configurations, including
   whether DEBUG mode is on.
