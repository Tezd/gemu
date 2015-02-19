<?hh // partial

namespace Gemu\Models;

use \LibSam\Cache\RedisCache;

class Account
{
    private RedisCache $store;

    public function __construct(
        private string $msisdn,
        private string $operator,
        private string $gateway,
        private string $country
    ) {

        $this->store = new RedisCache();
    }

    public function inbox(): Vector<Map<string, string> > {
        $data = $this->store->get($this->key());
        return new Vector(json_decode($data, true));
    }

    public function mo(string $text, string $to): void {
        $this->toInbox($text, $to, 'mo');
    }

    public function mt(string $text, string $from): void {
        $this->toInbox($text, $from, 'mt');
    }

    private function toInbox(string $text, string $shortcode, string $type): void {
        $inbox = $this->inbox();
        $inbox[] = Map {
            'shortcode' => $shortcode,
            'text' => $text,
            'type' => $type,
        };

        $this->save($inbox);
    }


    private function save(Vector<Map<string, string> > $messages): void {
        $this->store->set($this->key(), json_encode($messages));
    }

    private function key(): string {
        return strtolower(sprintf(
            "%s-%s-%s-%s",
            $this->msisdn,
            $this->operator,
            $this->gateway,
            $this->country
        ));
    }
}
