HTTP input/output
=================

Lightweight library to encapsulate PHPs different HTTP interfaces (`$_GET`,
`$_POST`, `header()` ...) in an object-oriented layer. Supportes input
validation, content negotiation and file uploads.


## Creating the request object

    $request = \itbz\httpio\Request::createFromGlobals();


## Input validation

The request object contains the following public properties: `headers`,
`cookies`, `query`, `body`. Each property is a `Laundromat` instance.

Reading from a laundromat always includes validation the data. This can be done
by using a `filter_var` filter.

    $acceptHeader = $request->headers->get('accept', FILTER_SANITIZE_STRING);

A regular expression

    $id = $request->query->get('id', '/^\d+$/');

Or a callback function

    $name = $request->body->get('name', 'ctype_alpha');


## Content negotiation

To negotiate first create a `Negotiator` object. The constructor takes an array
with types as keys and the server q-value as values (the server q-values are
currently ingored).

    $negotiator = new \itbz\httpio\Negotiator(array(
         'text/html' => 1.0,
         'application/xhtml+xml' => 1.0
    ));

Perform negotioation by calling negotiate with the proper header value
    
    $acceptHeader = $request->headers->get('Accept', FILTER_SANITIZE_STRING);
    $contentType = $negotiator->negotiate($acceptHeader);
    
    // Accept == 'application/xml,application/xhtml+xml,text/html;q=0.9'
    // yields 'application/xhtml+xml'

When negotiation content language you may wish to discard region informtion so
that `en-US` and `en-GB` are treated as just `en`.

    $negotiator = new \itbz\httpio\Negotiator(array(
         'sv'=>1.0,
         'en'=>1.0
    ));
    $arr = \itbz\httpio\Negotiator::parseRawAccept("en-US,en;q=0.9,sv;q=0.9");
    $arr = \itbz\httpio\Negotiator::mergeRegion($arr);
    $lang = $negotiator->negotiateArray($arr)
    //$lang == 'en'

## Working with uploaded files

    while ($upload = $request->getNextUpload()) {
        $upload->moveToDir('../yourdir');
    }

