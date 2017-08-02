---
title: Testing Escaping
extends: _layouts/index
section: content
---

<h3>{{ $page->getFilename() }}</h3>

Can we have php opening tags here?

*** convert all < to entitites after markdown processing is complete;
*** strip out all @verbatim/@endverbatim tags first, replace with placeholder, add back at very end;

@verbatim
    &lt;?php
    @if('something')
    @endif
@endverbatim

```
<?{{ '' }}php
```

<hr>
Start...
<hr>

1. This works: @include('_php') (where _php is a .md file)



```php
ok

Only an issue with blade.md files:


This works: <?php{{ '' }}

This does not work: {{ $page->php }}

This works: {!! $page->php !!}
This works: {!! '<' . '?php' !!}
This works: <{{ '?php' }}
This works: <?php{{ '' }}
This works: <?{{ '' }}php
This works: <?.php


ok

<? =
<>?=
<>?php
{!! '< ?php' !!}
{{ '<?php' }}

// Test comment...

@verbatim
@verbatim
stuff in here is rendered verbatim
@endverbatim@endverbatim

public function store()
{
    $test = true;
}
```
<hr>
End.
<hr>
