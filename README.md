An extension of The [Kohana 3 Auth module](http://github.com/kohana/auth) for use with [Jelly](http://github.com/jonathangeiger/kohana-jelly) User models.

This module is just a Jelly port of [Sprig Auth](http://github.com/banks/sprig-auth) driver by @[banks](http://github.com/banks) who in turn based his sprig-auth from Kohana's [ORM auth driver](http://github.com/kohana/auth).

I applied a **Critical difference** with Sprig Auth. This driver checks if the user still exists in the database. It's surprising that ORM Auth and Sprig Auth will still log the user in even if the user has already been deleted. They save and get the user object as a session.

Thanks to [Jonathan Geiger](http://github.com/jonathangeiger) for Jelly and Paul Banks for some tips.