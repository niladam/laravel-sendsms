<?php

namespace Niladam\LaravelSendsms;

use Exception;
use Illuminate\Support\Facades\Http;
use Niladam\LaravelSendsms\Exceptions\InvalidMessageProvidedException;
use Niladam\LaravelSendsms\Exceptions\InValidPhoneNumberProvidedException;
use Niladam\LaravelSendsms\Exceptions\UnknownOperationException;
use Throwable;

class LaravelSendsms
{
    protected string $username;
    protected string $password;
    protected string $url;
    protected array $operations = [];

    public function __construct(protected array $config = [])
    {
        $this->username = $this->config["username"];
        $this->password = $this->config["password"];
        $this->url = $this->config["url"];
        $this->operations = $this->config["operations"];
    }

    /**
     * @param  string  $to
     * @return array|string
     *
     * @throws Throwable
     */
    public function price(string $to = ""): array|string
    {
        $operationName = "price";

        throw_if(
            !$to,
            InValidPhoneNumberProvidedException::class,
            "No valid phone number provided."
        );

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

    public function call_api_action($method, $parameters): array|string
    {
        $params = $this->removeNumericKeysFromParameters($parameters);

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

    public function removeNumericKeysFromParameters(array $parameters): array
    {
        return array_filter(
            $parameters,
            static fn($key) => !is_numeric($key),
            ARRAY_FILTER_USE_KEY
        );
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

    public function balance()
    {
        $operationName = "balance";

        throw_if(
            !array_key_exists($operationName, $this->operations),
            UnknownOperationException::class,
            "No operation called $operationName found."
        );

        $operation = $this->operations[$operationName];

        $args = func_get_args() ?: [];

        return $this->call_api_action($operation, $args);
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
}
