<?php

namespace Niladam\LaravelSendsms;

use Exception;
use Illuminate\Support\Facades\Http;
use Niladam\LaravelSendsms\Exceptions\InvalidMessageProvidedException;
use Niladam\LaravelSendsms\Exceptions\InValidPhoneNumberProvidedException;
use ReflectionMethod;

class LaravelSendsms
{
    protected string $username;

    protected string $password;

    protected string $url;

    protected bool $performActionsImmediately = true;

    protected array $operations = [];

    public function __construct(protected array $config = [])
    {
        $this->username = $this->config["username"];
        $this->password = $this->config["password"];
        $this->url = $this->config["url"];
        $this->operations = $this->config["operations"];
    }

    public function price(string $to = "")
    {
        throw_if(
            !$to,
            InValidPhoneNumberProvidedException::class,
            "No valid phone number provided."
        );

        $operationName = __FUNCTION__;

        throw_if(
            !array_key_exists($operationName, $this->operations),
            UnknownOperationException::class,
            "No operation called $operationName found."
        );

        $operation = $this->operations[$operationName];

        $args = [
            "to" => $to,
        ];

        return $this->call_api_action($operation, $args);
    }

    public function call_api_action($method, $params): array|string
    {
        $url = $this->url . "?action=" . urlencode($method);
        $url .= "&username=" . urlencode($this->username);
        $url .= "&password=" . urlencode($this->password);

        foreach ($params as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if (is_bool($value)) {
                $url .=
                    "&" .
                    urlencode($key) .
                    "=" .
                    urlencode($value ? "true" : "false");
            } else {
                $url .= "&" . urlencode($key) . "=" . urlencode($value);
            }
        }

        return $this->sendRequest($url);
    }

    public function sendRequest($url): array|string
    {
        try {
            $response = Http::post($url)
                ->throw()
                ->json();

            return array_merge($response, $this->extractDataFromUrl($url));
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function extractDataFromUrl(string $url): array
    {
        $data = [];

        parse_str(parse_url(urldecode($url))["query"], $data);

        return $data["action"] === "message_send"
            ? [
                "to" => $data["to"],
                "from" => $data["from"],
                "message" => $data["text"],
            ]
            : [];
    }

    public function dump($str)
    {
        if ($this->$debug) {
            dump($str);
        }
    }

    /**
     *   This action allows you to check the price you can expect to pay for a message to the destination in 'to'
     *
     * @param  string  $to  : A phone number
     *
     * @global string $password
     * @global string $username
     */
    public function route_check_price($to)
    {
        $args = func_get_args();

        return $this->call_api_action(
            new ReflectionMethod(__CLASS__, __FUNCTION__),
            $args
        );
    }

    /**
     *   Gets the user balance
     *
     * @global string $username
     * @global string $password
     */
    public function user_get_balance()
    {
        $args = func_get_args();

        return $this->call_api_action(
            new ReflectionMethod(__CLASS__, __FUNCTION__),
            $args
        );
    }

    public function __call(string $name, array $arguments)
    {
        $operationName = $name;

        throw_if(
            !array_key_exists($operationName, $this->operations),
            UnknownOperationException::class,
            "No operation called $operationName found."
        );

        $operation = $this->operations[$operationName];

        $args = func_get_args() ?: [];

        return $this->call_api_action($operation, $args);
    }

    public function balance()
    {
        $operationName = __FUNCTION__;

        throw_if(
            !array_key_exists($operationName, $this->operations),
            UnknownOperationException::class,
            "No operation called $operationName found."
        );

        $operation = $this->operations[$operationName];

        $args = func_get_args() ?: [];

        return $this->call_api_action($operation, $args);
    }

    /**
     *   Gets the user details
     *
     * @global string $username
     * @global string $password
     */
    public function user_get_info()
    {
        $args = func_get_args();

        return $this->call_api_action(
            new ReflectionMethod(__CLASS__, __FUNCTION__),
            $args
        );
    }

    /**
     *   This function returns the verified phone number for the given user
     *
     * @global string $username
     * @global string $password
     */
    public function user_get_phone_number()
    {
        $args = func_get_args();

        return $this->call_api_action(
            new ReflectionMethod(__CLASS__, __FUNCTION__),
            $args
        );
    }

    public function send(string $to, string $message, ?string $from = "")
    {
        $operationName = __FUNCTION__;

        throw_if(
            !array_key_exists($operationName, $this->operations),
            UnknownOperationException::class,
            "No operation called $operationName found."
        );

        $operation = $this->operations[$operationName];

        throw_if(
            !$to,
            InValidPhoneNumberProvidedException::class,
            "Invalid, or no phone number provided. Got: $to"
        );

        throw_if(
            !$message,
            InvalidMessageProvidedException::class,
            "No message provided."
        );

        $args = [
            "to" => $to,
            "text" => $message,
            "from" => $from ?: $this->config["messages"]["from"],
            "report_mask" => $this->config["messages"]["report_mask"],
        ];

        if ($this->config["messages"]["callback_url"]) {
            $args["callback_url"] = $this->config["messages"]["callback_url"];
        }

        return $this->call_api_action($operation, $args);
    }

    /**
     *   Send an SMS message
     *
     * @param  string  $to
     * @param  string  $text  : The body of your message
     * @param  string  $from  (optional): The expeditor's label
     * @param  int  $report_mask  (optional): Delivery report request bitmask
     * @param  string  $report_url  (optional): URL to call when delivery status changes
     * @param  string  $charset  (optional): Character set to use
     * @param  int  $data_coding  (optional): Data coding
     * @param  int  $message_class  (optional): Message class
     * @param  int  $auto_detect_encoding  (optional): Auto detect the encoding and send appropriately 1 = on, 0 = off.
     * @param  string/boolean $short (optional): 1. "string" Add sort url at the end of message or search for key {short} in message and replace with short url when parameter contain URL
     *                                            2. "boolean" Searches long url and replaces them with coresponding sort url when shrot parameter is "true"
     *
     * @global string $username
     * @global string $password
     */
    public function message_send(
        $to,
        $text,
        $from = null,
        $report_mask = 19,
        $report_url = null,
        $charset = null,
        $data_coding = null,
        $message_class = -1,
        $auto_detect_encoding = null,
        $short = false
    ) {
        $args = func_get_args();

        return $this->call_api_action(
            new ReflectionMethod(__CLASS__, __FUNCTION__),
            $args
        );
    }
}
