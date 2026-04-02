import thailandPostLogo from '$lib/assets/logistics/thailand-post.png';
import kerryExpressLogo from '$lib/assets/logistics/kerry-express.png';
import flashExpressLogo from '$lib/assets/logistics/flash-express.png';
import jtExpressLogo from '$lib/assets/logistics/jt-express.png';
import thunderExpressLogo from '$lib/assets/logistics/thunder-express.png';

export interface DeliveryOption {
	value: string;
	label: string;
	note: string;
	logo: string;
}

export const deliveryOptions: DeliveryOption[] = [
	{
		value: 'thailand_post',
		label: 'Thailand Post',
		note: 'Estimated arrival: 3-4 days.',
		logo: thailandPostLogo
	},
	{
		value: 'kerry_express',
		label: 'Kerry Express',
		note: 'Estimated arrival: 1-2 days.',
		logo: kerryExpressLogo
	},
	{
		value: 'flash_express',
		label: 'Flash Express',
		note: 'Estimated arrival: 1-2 days.',
		logo: flashExpressLogo
	},
	{
		value: 'jt_express',
		label: 'J&T Express',
		note: 'Estimated arrival: 2-3 days.',
		logo: jtExpressLogo
	},
	{
		value: 'thunder_express',
		label: 'Thunder Express',
		note: 'Estimated arrival: just blink.',
		logo: thunderExpressLogo
	}
];

export function deliveryLabel(value: string | null | undefined) {
	return deliveryOptions.find((option) => option.value === value)?.label ?? value ?? 'Not selected';
}
