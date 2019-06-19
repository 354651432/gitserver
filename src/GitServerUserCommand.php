<?php


namespace Six\GitServer;


use Illuminate\Console\Command;

class GitServerUserCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = "git:user {action} {param?}";

    /**
     * @var string
     */
    protected $description = "git authorized user manage";

    /**
     * @var string
     */
    protected $table = "git_user";

    /**
     * GitServerUserCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = config("git.user_table", 'git_user');
    }

    /**
     *
     */
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

    /**
     * create authorized user table
     */
    public function actionInit()
    {
        \DB::select(<<<SQL
CREATE TABLE `{$this->table}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
        $this->info("table created");
    }

    /**
     * get authorized user list
     */
    public function actionList()
    {
        $users = \DB::table($this->table)->select("id", "email")->get()->toArray();
        $users = array_map("get_object_vars", $users);
        $this->table(["id", "email"], $users);
    }

    /**
     * add authorized user
     */
    public function actionAdd()
    {
        $param = $this->argument("param");
        if (empty($param)) {
            $this->error("eg: git:user add dual:123");
            return;
        }
        list($email, $password) = explode(":", $param);

        if (\DB::table($this->table)->whereEmail($email)->exists()) {
            $this->info("user existed");
            return;
        }
        \DB::table($this->table)->insert([
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $this->info("success");
    }

    /**
     * delete a user from authorized table
     */
    public function actionDelete()
    {
        $param = $this->argument("param");
        if (empty($param)) {
            $this->error("eg: git:user delete dual");
            return;
        }
        \DB::table($this->table)->whereEmail($param)->delete();

        $this->info("success");
    }
}
