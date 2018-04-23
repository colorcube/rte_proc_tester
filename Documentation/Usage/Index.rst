.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt



Usage
=====

1. Select the backend module *Web>RTE Processing Tester*.

2. Select the processing pipeline in the module menu:

    * DB > RTE
    * RTE > DB
    * DB > RTE > DB
    * RTE > DB > RTE

3. Select the page to get content from.

4. Edit the TypoScript and click *save* to change the configuration.


.. figure:: ../Images/screenshot.png

    the backend module in action


Beware! There's no undo. The TypoScript is saved inside the extension.

When your configuration is finished, copy the TypoScript to the desired location.

Content to process
-------------------

The content to process will be fetched from the database (content elements). This is usually RTE text which is the right
thing to test DB > RTE processing.

To test the other processing pipelines, add pages with other content, like html content elements with ugly pasted Word html.

If you want to test RTE > DB processing you can put a HTML content element on the page with the html code from
inside the RTE. The module will use that content for testing.



