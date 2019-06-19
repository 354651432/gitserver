<?php


namespace Six\GitServer;


use Illuminate\Console\Command;

class GitServerUserCommand extends Command
{
    protected $signature = "git:user {action} {param?}";

    protected $description = "git authorized user manage";

    protected $table = "";

    public function handle()
    {
        $action = $this->argument("action");
        $action = "action" . ucfirst($action);
        if (method_exists($this, $action)) {
            call_user_func([$this, $action]);
            return;
        }
        $this->error(<<<STR
actions:
   . init
   . list
   . add
   . delete
STR
        );
    }

    public function actionInit()
    {
        \DB::select(<<<SQL
CREATE TABLE `git_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
        $this->info("table created");
    }

    public function actionList()
    {
        $users = \DB::table("git_user")->select("id", "email")->get()->toArray();
        $users = array_map("get_object_vars", $users);
        $this->table(["id", "email"], $users);
    }

    public function actionAdd()
    {
        $param = $this->argument("param");
        if (empty($param)) {
            $this->error("eg: git:user add dual:123");
            return;
        }
        list($email, $password) = explode(":", $param);

        if (\DB::table("git_user")->whereEmail($email)->exists()) {
            $this->info("user existed");
            return;
        }
        \DB::table("git_user")->insert([
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $this->info("success");
    }

    public function actionDelete()
    {
        $param = $this->argument("param");
        if (empty($param)) {
            $this->error("eg: git:user delete dual");
            return;
        }
        \DB::table("git_user")->whereEmail($param)->delete();

        $this->info("success");
    }
}
