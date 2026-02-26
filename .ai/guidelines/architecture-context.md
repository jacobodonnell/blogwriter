=== architecture context ===

# BlogWriter Architecture Context

## Mass Assignment — No `$fillable` Needed

`nunomaduro/essentials` is installed with `Unguard::class => true` in `config/essentials.php`.
This calls `Model::unguard()` globally at boot — no model needs `$fillable` or `$guarded`.
Do NOT flag missing `$fillable` as a security issue. It is intentional and correct.
