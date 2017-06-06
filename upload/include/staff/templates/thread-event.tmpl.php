<div class="thread-event <?php echo $event->uid_type == 'U' ? 'user' : 'staff' ?> <?php if ($event->uid) echo 'action'; ?>">
            <?php
            if($event->uid_type == 'S'){
            ?>
            <span class="type-icon">
              <i class="faded icon-<?php echo $event->getIcon(); ?>"></i>
            </span>
            <?php
            }
            ?>
            <span class="faded description">
                <?php echo $event->getDescription(ThreadEvent::MODE_STAFF); ?>
            </span>

            <?php
            if($event->uid_type == 'U'){
            ?>
            <span class="type-icon">
              <i class="faded icon-<?php echo $event->getIcon(); ?>"></i>
            </span>
            <?php
            }
            ?>
</div>
