{% include 'partials/nav.volt' %}

<div class="mgrid">
    {{ flash.output() }}
    {{ content() }}
    <hr>
    <footer class='clear'>
<p>&copy; Parracan | 
        <?php
            echo " Response time " . sprintf('%.2f ms', (microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
            echo " | Memory " . sprintf('%.2f MiB', memory_get_peak_usage() / 1024 / 1024);
        ?>
</p>
    </footer>
</div>