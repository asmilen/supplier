# Supplier Tools

## Gửi / Nhận Message

Thêm cấu hình trong file `.env`:

    AMQP_HOST=white-mynah-bird.rmq.cloudamqp.com
    AMQP_PORT=5672
    AMQP_USER=cctrjfkj
    AMQP_PASSWORD=9oDiGIC-9NWabuXTTQhLaqXVs5gahFxc
    AMQP_VHOST=cctrjfkj


### Nhận Message

Để nhận message từ exchange 'test-exchange', routing key 'wh.import.create':


    php artisan test-exchange wh.import.create

Dùng `supervisord` để nhận message trên production.

### Gửi Message

Gửi message đến exchange `'test-exchange'`, routing key `'sale.order.create'`, body `'{"foo":"zzzzz","bar":2}'`:


    use App\Jobs\PublishMessage;

    ...
    
    class OrderController extends Controller
    {
        ...
        
        public function store()
        {
            ...
            
            dispatch(new PublishMessage('test-exchange', 'sale.order.create', '{"foo":"zzzzz","bar":2}'));
            
            ...
        }
    }