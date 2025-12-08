// pusherManager.js

class PusherManager {
    constructor(appKey, cluster) {
        if (!appKey || !cluster) {
            console.error("Pusher key or cluster missing!");
            return;
        }

        // Initialize Pusher
        this.pusher = new Pusher(appKey, { cluster: cluster });
        this.channels = {}; // store subscribed channels
    }

    // Debounce helper (reusable)
    static debounce(func, delay = 200) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Subscribe to a channel
    subscribe(channelName) {
        if (!this.channels[channelName]) {
            this.channels[channelName] = this.pusher.subscribe(channelName);
        }
        return this.channels[channelName];
    }

    // Bind an event with optional debounce
    bind(channelName, eventName, callback, debounceDelay = 0) {
        const channel = this.subscribe(channelName);

        if (debounceDelay > 0) {
            const debouncedCallback = PusherManager.debounce(callback, debounceDelay);
            channel.bind(eventName, debouncedCallback);
        } else {
            channel.bind(eventName, callback);
        }
    }
}


// // Initialize manager
// const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

// // Fetch users on add or update
// pusherManager.bind('users-channel', 'modify-user', fetchuser, 200);

// // Fetch orders on new order
// pusherManager.bind('orders-channel', 'order-added', fetchOrders, 300);

// // Any other channel/event
// pusherManager.bind('products-channel', 'product-updated', fetchProducts);

