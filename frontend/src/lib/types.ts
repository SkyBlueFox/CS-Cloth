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

export interface WalletTransaction {
	id: number;
	type: string;
	provider: string | null;
	status: string;
	amount: number;
	balance_before: number;
	balance_after: number;
	reference: string;
	note: string | null;
	completed_at: string | null;
	created_at: string | null;
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
	current_user_report_status: 'pending' | 'resolved' | 'dismissed' | null;
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
	item: Item | null;
	created_at: string | null;
}

export interface OrderLine {
	id: number;
	item_id: number;
	quantity: number;
	price_at_purchase: number;
	refund_requested_quantity: number;
	refunded_quantity: number;
	refund_dismissed_quantity: number;
	refundable_quantity: number;
	refund_reason_code: string | null;
	refund_reason_detail: string | null;
	refund_issue_description: string | null;
	refund_evidence_image_path: string | null;
	refund_evidence_image_url: string | null;
	refund_requested_at: string | null;
	refund_approved_at: string | null;
	refund_dismissed_at: string | null;
	refund_events: RefundEvent[];
	item: Item | null;
}

export interface RefundEvent {
	id: number;
	event_type: 'requested' | 'approved' | 'dismissed' | string;
	quantity: number;
	reason_code: string | null;
	reason_detail: string | null;
	issue_description: string | null;
	evidence_image_path: string | null;
	evidence_image_url: string | null;
	acted_by_user_id: number | null;
	happened_at: string | null;
}

export interface OrderStatusEvent {
	key: string;
	label: string;
	status: string;
	timestamp: string;
}

export interface Order {
	timeline: never[];
	id: number;
	order_number: string;
	buyer_id: number;
	status: string;
	total_price: number;
	shipping_address: string | null;
	shipping_address_id: number | null;
	shipping_address_snapshot: Omit<Address, 'id' | 'is_default' | 'formatted'> | null;
	shipping_address_formatted: string | null;
	delivery_method: string | null;
	delivery_method_label: string | null;
	buyer: User | null;
	items: OrderLine[];
	shipped_at: string | null;
	cancelled_at: string | null;
	refund_requested_at: string | null;
	refunded_at: string | null;
	created_at: string | null;
	updated_at: string | null;
	status_history: OrderStatusEvent[];
}

export interface CartItem extends Item {
	quantity: number;
}
