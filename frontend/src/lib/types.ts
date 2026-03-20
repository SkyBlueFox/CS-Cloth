export type Role = 'user' | 'admin' | 'superadmin';

export interface User {
	id: number;
	name: string;
	email: string;
	phone: string | null;
	role: Role;
	balance: number;
	created_at: string | null;
	deleted_at: string | null;
}

export interface Address {
	id: number;
	label: string;
	recipient_name: string;
	phone: string;
	line_1: string;
	line_2: string | null;
	district: string;
	province: string;
	postal_code: string;
	country: string;
	is_default: boolean;
	formatted: string;
}

export interface PaginationMeta {
	current_page: number;
	last_page: number;
	per_page: number;
	total: number;
}

export interface Paginated<T> {
	data: T[];
	meta: PaginationMeta;
}

export interface Item {
	id: number;
	name: string;
	description: string | null;
	price: number;
	stock: number;
	image_path: string | null;
	image_url: string | null;
	is_active: boolean;
	created_at: string | null;
	updated_at: string | null;
}

export interface Question {
	id: number;
	item_id: number;
	asker_id: number;
	asker_name: string;
	admin_id: number | null;
	admin_name: string | null;
	question_text: string;
	answer_text: string | null;
	score_cached: number;
	is_reported_by_current_user: boolean;
	item: Item | null;
	asker: User | null;
	created_at: string | null;
	updated_at: string | null;
}

export interface Report {
	id: number;
	reporter_id: number | null;
	reporter_name: string;
	admin_id: number | null;
	admin_name: string;
	question_id: number | null;
	question_text_snapshot: string;
	answer_text_snapshot: string;
	reason: string;
	status: string;
	created_at: string | null;
}

export interface OrderLine {
	id: number;
	item_id: number;
	quantity: number;
	price_at_purchase: number;
	item: Item | null;
}

export interface Order {
	id: number;
	buyer_id: number;
	status: string;
	total_price: number;
	shipping_address: string | null;
	shipping_address_id: number | null;
	shipping_address_snapshot: Omit<Address, 'id' | 'is_default' | 'formatted'> | null;
	shipping_address_formatted: string | null;
	buyer: User | null;
	items: OrderLine[];
	shipped_at: string | null;
	cancelled_at: string | null;
	refund_requested_at: string | null;
	refunded_at: string | null;
	created_at: string | null;
}

export interface CartItem extends Item {
	quantity: number;
}