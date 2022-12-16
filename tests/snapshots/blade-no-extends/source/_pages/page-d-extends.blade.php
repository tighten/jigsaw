---
extends: _layouts.main
---
@foreach ([1] as $i)
Extends but no section, renders incorrectly (at the top of the file, intead of at the `yield` directive).
@endforeach
