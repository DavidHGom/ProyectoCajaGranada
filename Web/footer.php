<?php

/**
* Archivo footer.php que controla la impresión del pie de la página
* @author Manuel Lafuente Aranda
*/

/**
 * Clase footer con la que se imprime el contenido del mismo
 */
class footer{

  function pintar(){
    echo '<footer class="text-center">
            <img src="/imgs/CajaGranada.jpg" width="250" height="250" />
        </footer>
        <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/checkers_content.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="assets/js/ie10-viewport-bug-workaround.js"></script>
      </body>
    </html>';
  }
}

?>
