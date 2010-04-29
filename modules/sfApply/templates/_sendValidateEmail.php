<?php use_helper('I18N', 'Url') ?>
<?php echo __('<p>
Hello %USERNAME%!
</p><p>
We have received your request to change your email address on: %1%
</p><p>
You\'re goint to change your email from: %OLDEMAIL% to %NEWEMAIL%
</p><p>
To continue with your change, click on the link that follows:
</p><p>
%2%
</p><p>
Your email will then be changed pemanently.
</p>'
, array(
  "%1%" => link_to($sf_request->getHost(), $sf_request->getUriPrefix()),
  "%2%" => link_to(url_for("sfApply/confirm?validate=$validate", true), "sfApply/confirm?validate=$validate", array("absolute" => true)),
  "%USERNAME%" => $username,
  "OLDEMAIL" => $odlemail,
  "%NEWEMAIL" => $newemail
  ),
    'sfForkedApply') ?>

