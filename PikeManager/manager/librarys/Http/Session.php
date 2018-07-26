<?php

    namespace Librarys\Http;

    use Librarys\Http\Exception\SessionException;

    class Session
    {

        private static $instance;
        private $isSessionStarted;

        private function __construct()
        {
            self::$instance = $this;
        }

        private function __clone()
        {

        }

        private function __wakeup()
        {

        }

        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new Session();

            if (self::$instance instanceof Session || self::$instance->isSessionStarted == false)
                self::$instance->start();

            return self::$instance;
        }

        public function start()
        {
            $this->isSessionStarted = false;

            if (env('app.session.init', false) == false)
                throw new SessionException('Session not enable in config app', 1);

            $sessionStatus = false;

            if (version_compare(phpversion(), '5.4.0', '>='))
                $sessionStatus = session_status() === PHP_SESSION_ACTIVE;
            else
                $sessionStatus = session_id() !== '';

            if ($sessionStatus == false) {
                $name         = env('app.session.name',          null);
                $cacheLimiter = env('app.session.cache_limiter', null);
                $cacheExpire  = env('app.session.cache_expire',  null);

                if (empty($name))
                    throw new SessionException('Name session in config is empty', 1);

                if (is_numeric($cacheExpire) == false)
                    throw new SessionException('Cache expire session not validate', 1);

                session_name($name);
                session_cache_limiter($cacheLimiter);
                session_cache_expire($cacheExpire);

                $cookieLifetime = env('app.session.cookie_lifetime', 180);
                $cookiePath     = env('app.session.cookie_path', '/');

                if (is_numeric($cookieLifetime) == false)
                    throw new SessionException('Cookie lifetime session not validate', 1);

                session_set_cookie_params(
                    $cookieLifetime,
                    $cookiePath
                );

                if (session_start() == false)
                    throw new SessionException('Cannot start session', 1);
            }

            $this->isSessionStarted = true;
        }

        public function get($key)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if ($this->has($key))
                return $_SESSION[$key];

            return null;
        }

        public function has($key)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            return isset($_SESSION[$key]);
        }

        public function put($key, $value, $isPutArray = false)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if ($isPutArray == false)
                $_SESSION[$key] = $value;
            else
                $_SESSION[$key][] = $value;
        }

        public function remove($key)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if (isset($_SESSION[$key]))
                unset($_SESSION[$key]);
        }

        public function destroy()
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if (session_destroy() == false)
                return false;
            else
                $this->isSessionStarted = false;

            return true;
        }

    }
