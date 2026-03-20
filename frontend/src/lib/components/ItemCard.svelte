<script context="module" lang="ts">
	export type Item = {
		id: number;
		name: string;
		price: number;
		image_path?: string;
	};
</script>

<script lang="ts">
	import { addToCart } from '$lib/stores/cart';
	let { item } = $props<{ item: Item }>();
</script>

<div class="border p-4 rounded shadow">
	<img
		src={item.image_path || '/placeholder.png'}
		alt={item.name}
		class="w-full h-40 object-cover"
	/>
	<h2 class="text-lg font-bold mt-2">{item.name}</h2>
	<p class="text-gray-600">{item.price} ฿</p>

	<div class="flex gap-2 mt-2">
		<a href={`/shop/${item.id}`} class="text-blue-500">
			View
		</a>
		<button class="bg-green-500 text-white px-2 py-1 rounded" onclick={() => addToCart(item)}>
			Add to cart
		</button>
	</div>
</div>