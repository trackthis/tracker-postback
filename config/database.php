<?php

// This is read by the url parser
if(!getenv('DATABASE_URL')) {
    putenv('DATABASE_URL=mysql://user:password@localhost:3306/database');
}

return array(
    'database' => PicoDb\UrlParser::getInstance()->getSettings(),
);
