httpio
======

Simple HTTP input/output library

Create Request object from normal PHP sources

    new Request(
         $_SERVER['REMOTE_ADDR'],
         $_SERVER['REQUEST_URI'],
         $_SERVER['REQUEST_METHOD'],
         getallheaders(),
         $_COOKIE,
         $_GET,
         $_POST,
         $_FILES
    );

Negotiate content type

    $n = new Negotiator(array(
         'text/html' => 1.0,
         'application/xhtml+xml' => 1.0
    ));
    $ctype = $n->negotiate("application/xml,application/xhtml+xml,text/html;q=0.9");
    //$ctype == 'application/xhtml+xml'

Negotiate language discarding regional information

    $n = new Negotiator(array(
         'sv'=>1.0,
         'en'=>1.0
    ));
    $arr = Negotiator::parseRawAccept("en-US,en;q=0.9,sv;q=0.9");
    $arr = Negotiator::mergeRegion($arr);
    $lang = $n->negotiateArray($arr)
    //$lang == 'en'

Uploaded files

    $u = new Upload(
         $_FILES['foobar']['name'],
         $_FILES['foobar']['tmp_name'],
         $_FILES['foobar']['size'],
         $_FILES['foobar']['type'],
         $_FILES['foobar']['error'],
    );
    $u->moveToDir('dirname');

