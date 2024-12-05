    <?php
    require_once "./config/config.php";
    require_once "./core/DatabaseException.php";
    require_once "./core/Database.php";
    require_once "./core/FileDatabase.php";
    require_once "./core/AuthDatabase.php";
    require_once "./core/Redirector.php";
    require_once "./core/Controller.php";
    require_once "./core/ResponseHandler.php";
    require_once "./core/Helper.php";
    require_once "./middleware/AuthenMiddleware.php";
    require "./core/Router.php";

    session_start();
    
    try {
        $router = new Router();
        $router->route();
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
    ?>