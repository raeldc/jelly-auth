An extension of The [Kohana 3 Auth module](http://github.com/kohana/auth) for use with [Jelly](http://github.com/jonathangeiger/kohana-jelly) User models.

This module is just a Jelly port of [Sprig Auth](http://github.com/banks/sprig-auth) driver by @[banks](http://github.com/banks) who in turn based his sprig-auth from Kohana's [ORM auth driver](http://github.com/kohana/auth).

Jelly Auth used to implement **something different** from Sprig Auth and ORM Auth. Upon using Auth::instance()->logged_in(), Jelly Auth used to check if the user still exists in the database. 
It's surprising that ORM Auth and Sprig Auth will still log the user in even if the user has already been deleted. They save and get the user object as a session. 

In Jelly Auth, I wanted to check if the user exists before authenticating. But I realized that it's not really this Driver's place to change the way how the default Auth works. Auth is supposed to be very basic. I'll just create a more advanced Auth Module that adds more advanced checks.

Thanks to [Jonathan Geiger](http://github.com/jonathangeiger) for Jelly and Paul Banks for many helpful tips.