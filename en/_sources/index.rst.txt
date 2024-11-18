Alto (Omeka S module)
=====================

This Omeka S module allows to attach ALTO (XML) files to media. The ALTO
content can then be used in several ways (for display, search, ...).

Requirements
------------

-  Omeka S ≥ 3.2.0 (or ≥ 4.0.0 if you need resource page blocks)
-  For search capabilities, modules
   `Search <https://github.com/biblibre/omeka-s-module-Search>`__ and
   `Solr <https://github.com/biblibre/omeka-s-module-Solr>`__

Quick start
-----------

1. `Install the
   module <https://omeka.org/s/docs/user-manual/modules/#adding-modules-to-omeka-s>`__
2. In the admin interface, on a media page there is a new tab "ALTO".
   Click the tab, then click on "Attach an ALTO document". In the
   sidebar, select an ALTO file and submit the form.
3. To learn how to exploit ALTO files (display, search, ...), go to the table
   of contents at the end of this page.

Features
--------

-  Import ALTO files one by one or by batch
-  Resource page block rendering ALTO content as plain text (requires
   Omeka S ≥ 4.0.0)
-  Integration with the
   `Solr <https://github.com/biblibre/omeka-s-module-Solr>`__ module
   (makes the ALTO plain text content searchable)

Caveats / Known problems
------------------------

-  Batch import is very limited at the moment: it requires that the ALTO
   file has the same basename than the media filename (for instance,
   ``PAGE0001.png`` and ``PAGE00001.xml``). This should change in a
   future version

How to contribute
-----------------

-  If you found a bug or want to suggest a new feature, please `open an
   issue <https://github.com/biblibre/omeka-s-module-Alto/issues/new>`__.
-  If you made a modification to the module (bugfix, enhancement, ...)
   and want to make it available to everyone, `open a pull
   request <https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request>`__.
   To increase its chances of being reviewed and merged, please keep the
   pull request small:

   -  only one change per pull request (eg. do not mix a bugfix with a
      new feature),
   -  include only necessary changes (eg. no code style changes)

Contributors / Sponsors
-----------------------

This module was sponsored by:

-  `Médiathèques d'Orléans <https://mediatheques.orleans.fr/>`__

License
-------

This module is distributed under the GNU General Public License, version
3 (GPLv3). The full text of this license is given in the ``LICENSE``
file.

.. toctree::
   :glob:
   :caption: User manual

   user/*
