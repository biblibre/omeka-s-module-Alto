# Alto (Omeka S module)

This Omeka S module allows to attach ALTO (XML) files to media. The ALTO
content can then be used in several ways (for display, search, ...).

## Requirements

- Omeka S ≥ 3.2.0 (or ≥ 4.0.0 if you need resource page blocks)
- For search capabilities, modules
  [Search](https://github.com/biblibre/omeka-s-module-Search) and
  [Solr](https://github.com/biblibre/omeka-s-module-Solr)

## Quick start

1. [Install the module](https://omeka.org/s/docs/user-manual/modules/#adding-modules-to-omeka-s)
2. In the admin interface, on a media page there is a new tab "ALTO". Click the
   tab, then click on "Attach an ALTO document". In the sidebar, select an ALTO
   file and submit the form.
3. To learn how to exploit ALTO files (display, search, ...), go to the
   [full documentation](https://biblibre.github.io/omeka-s-module-Alto/).

## Features

- Import ALTO files one by one or by batch
- Resource page block rending ALTO content as plain text (requires Omeka S ≥ 4.0.0)
- Integration with the [Solr](https://github.com/biblibre/omeka-s-module-Solr)
  module (makes the ALTO plain text content searchable)

## Caveats / Known problems

- Batch import is very limited at the moment: it requires that the ALTO file
  has the same basename than the media filename (for instance, `PAGE0001.png`
  and `PAGE00001.xml` or `PAGE00001.alto.xml`). This should change in a future
  version

## How to contribute

- If you found a bug or want to suggest a new feature, please
  [open an issue](https://github.com/biblibre/omeka-s-module-Alto/issues/new).
- If you made a modification to the module (bugfix, enhancement, ...) and want
  to make it available to everyone,
  [open a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request).
  To increase its chances of being reviewed and merged, please keep the pull request small:
  - only one change per pull request (eg. do not mix a bugfix with a new feature),
  - include only necessary changes (eg. no code style changes)

## Contributors / Sponsors

This module was sponsored by:
- [Médiathèques d'Orléans](https://mediatheques.orleans.fr/)

## License

This module is distributed under the GNU General Public License, version 3
(GPLv3).  The full text of this license is given in the `LICENSE` file.
