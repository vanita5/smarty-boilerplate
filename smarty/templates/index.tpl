<!DOCTYPE html>
<!--suppress HtmlUnknownTarget -->
<html>
    <head>
        <title>{#title#}</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <link rel="shortcut icon" href="favicon.ico" />
        <link rel="stylesheet" href="assets/css/style.css" />
    </head>
    <body>
        <header>
            <h1>{#title#}</h1>
            <nav>
                <ol>
                    <li>Home</li>
                    <li>About</li>
                </ol>
            </nav>
        </header>
        <div id="content">
            <p>content n stuff</p>
        </div>
        {include file="include/footer.tpl"}

        <script src="assets/js/jquery-2.2.3.min.js"></script>
        <script src="assets/js/application.js"></script>
    </body>
</html>
