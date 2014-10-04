<html>
<head>
  <meta charset="UTF-8">
  <title>Error</title>

  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400" />

  <style>

  *, *:before, *:after {
    margin: 0;
    padding: 0;
    border: 0;
    background-repeat: no-repeat;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }

  html {
    background: #222;
  }

  body {
    padding: 3em;
    color: #fff;
    font-family: 'Source Sans Pro', helvetica, sans-serif;
    font-size: 1.25em;
  }

  h1 {
    margin-bottom: .5em;
    font-weight: 300;
  }

  .error {
    line-height: 1.5em;
  }
  .error-details {
    border-top: 2px solid #333;
  }
  .error-details dt {
    color: #999;
    padding: .75em 0 0;
  }
  .error-details dd {
    border-bottom: 2px solid #333;
    padding: 0 0 .75em;
    font-weight: 300;
  }
  .error-details dd:last-child {
    border-bottom: 0;
  }

  code {
    font-family: 'Courier New', monospace;
    font-size: 1.15em;
  }
  .code-line {
    display: block;
    padding: 0 1.5em 0 0;
    line-height: 2em;
    width: 100%;
    height: 2em;
  }
  .code-line:nth-child(odd) {
    background: #151515;
  }
  .code-line-number {
    display: inline-block;
    width: 3em;
    padding-left: 1em;
    color: #555;
  }
  .code-line-highlighted {
    background: #b3000a !important;
    display: block;
    font-weight: normal;
    color: #fff;
  }
  .code-line-highlighted .code-line-number {
    color: #fff;
  }
  .code {
    color: #999;
    background: #000;
    margin-top: 1em;
    overflow: auto;
  }

  </style>

</head>
<body>

  <h1>Error</h1>

  <div class="error">
    <dl class="error-details">
      <dt>Message</dt>
      <dd><?php echo $message ?></dd>
      <dt>File</dt>
      <dd><?php echo $file ?></dd>
      <dt>Line</dt>
      <dd><?php echo $line ?></dd>
      <dt>Code</dt>
      <dd>
        <pre class="code"><code><?php echo $extract ?></code></pre>
      </dd>
      <?php if($kirby): ?>
      <dt>Kirby Version</dt>
      <dd><?php echo $kirby->version() ?></dd>
      <?php endif ?>
    </dl>
  </div>

</body>
</html>