
</div>
</div>
</div>
</div>
</div>

<?php 

    if (isset($footer['js'])){
        for ($i = 0; $i < count($footer['js']); $i++) {
            if (strpos($footer['js'][$i], "https://") !== FALSE || strpos($footer['js'][$i], "http://") !== FALSE)
                echo '<script type="text/javascript" src="' . $footer['js'][$i] . '"></script>';
            else
                echo '<script type="text/javascript" src="' . \URL::to('assets/js/' . $footer['js'][$i]) . '"></script>';
        }
    }

?>

@yield('footer')
</body>

</html>