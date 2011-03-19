<a name="signup"></a>
<?php
Debugger::log(MessageLogger::getMessages());
if (MessageLogger::getMessages()):
  foreach (MessageLogger::getMessages() as $type => $messages):
    foreach ($messages as $message):
      ?>
      <div class="<?= $type ?>"><?= $message ?></div>
      <?php
    endforeach;
  endforeach;
endif;
?>