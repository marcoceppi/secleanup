<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>StackExchange Cleanup</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>

    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>

  <body>
    <div class="container">
      <h1>Ask Ubuntu Site Tracker</h1>
      <p>It's no surprise, a site as popular as Ask Ubuntu was bound to attrack a little bit of cruft. However, because of this our site statistics don't quite reflect how awesome we really are. 
	This site is designed to provide up-to-the-minute statistics on our battle to reclaim
	our rightful spot as the best source for high quality Q&A for the Ubuntu project.</p>
      <p>Below is our current spread of information, where we are now and what we have left to do 
	in order to reach our next goal. If you're interested in helping please read the following 
	post on <a href="http://meta.askubuntu.com/">meta</a>.</p>
      <p></p>
      <hr>
      <div class="row">
        <div class="span4">
          <h2>Acceptance Rate</h2>
          <p>Currently <big><?php echo $curr_accept_rate; ?></big>% questions are considered "answered" on our site. Out of <?php echo number_format($questions); ?> questions, <big><?php echo number_format($unanswered); ?></big> remain unanswered. In order to have an accepted answer rate of <big><?php echo $next_percentage; ?></big>% we need to "answer" <big><?php echo number_format($questions_till_we_rule_the_next_percentage); ?></big> more questions.</p>
          <div class="progress" style="margin-bottom:0;">
            <div class="bar" style="width: <?php echo $percent_complete_to_next_percent; ?>%"></div>
          </div>
          <p><small>Progress to <?php echo $next_percentage; ?>% answered rate.</small></p>
        </div>
        <div class="span4">
          <h2>Additional Crap</h2>
          <p>We can put even more metrics here. Might as well make this more useful.</p>
        </div>
      </div>
      <hr>
      <footer>Site by <a href="http://marcoceppi.com/" target="_blank">Marco Ceppi</a>, hosted on <a href="http://ondina.co" target="_blank">Ondina</a>. Made with StackPHP and Bootstrap. <small>Legal stuff.</small></footer>
    </div>
    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
  </body>
</html>
