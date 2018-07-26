<!DOCTYPE html>
<html>
    <head>

        <?php if ($object->isErrors) { ?>
            <title><?php echo $object->label; ?></title>
        <?php } else { ?>
            <title><?php echo $object->message; ?></title>
        <?php } ?>

        <meta http-equiv="Content-Type" content="text/html; charset=uft-8"/>
        <meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">
        <meta http-equiv="Cache-Control" content="private, max-age=0, no-cache, no-store, must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta name="robots" content="noindex, nofollow, noodp, nodir"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=false"/>

        <style type="text/css">
            body {
                font-family: Verdana, Geneva, sans-serif;
                font-size: 15px;
                background-color: #cfcfcf;
                margin: 0;
                padding: 50px;
            }

            h2 {
                margin: 0;
                padding: 0;
                font-weight: normal;
            }

            div.container {
                background-color: #ffffff;
                box-shadow: 1px 1px 10px rgba(0, 0, 0, 0.2);
            }

            div.error {
                color: #ffffff;
                background-color: #505050;
                padding: 20px;
                word-wrap: break-word;
            }

            ul.list, ul.list li {
                list-style: decimal;
                margin-top: 0;
                margin-bottom: 0;
            }

            ul.list li {
                color: #505050;
                margin-left: 0;
                padding: 8px;
                padding-left: 0;
                padding-right: 15px;
                word-wrap: break-word;
            }

            span.file {
                color: #808080;
            }

            span.class, span.resource {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="container">

            <div class="error">
                <h2>
                    <?php if ($object->class != null) { ?>
                        <?php echo $object->class; ?>:
                    <?php } ?>

                    <?php if ($object->isErrors) { ?>
                        <?php echo $object->label; ?>:
                    <?php } ?>

                    <?php echo $object->message; ?>
                </h2>
            </div>

            <ul class="list">
                <li>in <span class="file"><?php echo $object->file; ?> line <strong class="line"><?php echo $object->line; ?></strong></span></li>

                <?php $entryClass    = null; ?>
                <?php $entryType     = null; ?>
                <?php $entryFunction = null; ?>
                <?php $entryFile     = null; ?>
                <?php $entryLine     = null; ?>

                <?php if (is_array($object->trace) && count($object->trace) > 0) { ?>
                    <?php foreach ($object->trace AS $i => $entry) { ?>
                        <?php if (isset($entry['class']))    $entryClass    = $entry['class']; ?>
                        <?php if (isset($entry['type']))     $entryType     = $entry['type']; ?>
                        <?php if (isset($entry['function'])) $entryFunction = $entry['function'] . '(' . Librarys\Error\ErrorDisplay::argsExport($entry['args']) . ')'; ?>
                        <?php if (isset($entry['file']))     $entryFile     = ' in <span class="file">' . $entry['file']; ?>
                        <?php if (isset($entry['line']))     $entryLine     = ' line <strong class="line">' . $entry['line'] . '</strong></span>'; ?>

                        <li>at <?php echo $entryClass . $entryType . $entryFunction . $entryFile . $entryLine; ?></li>

                        <?php $entryClass    = null; ?>
                        <?php $entryType     = null; ?>
                        <?php $entryFunction = null; ?>
                        <?php $entryFile     = null; ?>
                        <?php $entryLine     = null; ?>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    </body>
</html>
