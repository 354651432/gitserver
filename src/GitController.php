<?php

namespace Six\GitServer;

use Illuminate\Routing\Controller;

class GitController extends Controller
{
    /**
     * base path
     * @var string
     */
    private $gitRoot;

    /**
     * GitController constructor.
     */
    public function __construct()
    {
        $this->gitRoot = config("git.basepath", storage_path("repos"));
    }

    /**
     * init a git repos
     * @param $name
     * @return bool|string
     */
    public function init($name)
    {
        $repo_path = $this->gitRoot . '/' . $name . ".git";
        $cmd = "git init --bare {$repo_path}";

        return self::procExec($cmd);
    }

    /**
     * proc get request from git
     * @param $name
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getInfoRefs($name)
    {
        $service = trim($_GET['service'], 'git-');
        $repo_path = $this->gitRoot . '/' . $name . '.git';

        if (in_array($service, ['upload-pack', 'receive-pack'])) {
            self::updateServerInfo($repo_path);
        }

        $cmd = "git $service --stateless-rpc --advertise-refs $repo_path";
        $out = shell_exec($cmd);
        $res = self::packetWrite("# service=git-$service");
        $res .= $out;

        return response($res)
            ->header("Content-Type", "application/x-git-$service-advertisement");
    }

    /**
     * proc post request from git
     * @param $name
     * @param $service
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function command($name, $service)
    {
        $input = file_get_contents('php://input');

        $repo_path = $this->gitRoot . '/' . $name . '.git';
        $cmd = "git $service --stateless-rpc $repo_path";
        $res = self::procExec($cmd, $input);
        if ('receive-pack' == $service) {
            self::updateServerInfo($repo_path);
        }

        return response($res)
            ->header("Content-Type", "application/x-git-$service-result");
    }

    /**
     * when git push should update server info
     * @param $repo_path
     */
    public static function updateServerInfo($repo_path)
    {
        $cmd = "git --git-dir $repo_path update-server-info";
        file_put_contents('updateServerInfo', $cmd);
        shell_exec($cmd);
    }

    /**
     * @param $str
     * @return string
     */
    public static function packetWrite($str)
    {
        $len = dechex(strlen($str) + 4);
        $len = str_pad($len, 4, "0", STR_PAD_LEFT);
        return "{$len}{$str}0000";
    }

    /**
     * execute a git command
     * @param $cmd
     * @param null $input
     * @return bool|string
     */
    public static function procExec($cmd, $input = null)
    {
        $proc = proc_open($cmd, [['pipe', 'r'], ['pipe', 'w']], $pipes);
        if (!is_resource($proc)) {
            return false;
        }

        $input && fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $res = '';
        while (!feof($pipes[1])) {
            $res .= fread($pipes[1], 8192);
        }
        fclose($pipes[1]);
        proc_close($proc);

        return $res;
    }
}
