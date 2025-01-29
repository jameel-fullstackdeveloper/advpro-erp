

<!-- History -->
<div class="d-none">
    <h5>Order History</h5>
    <ul>
        @foreach ($logs as $log)
            <li>
                <strong>{{ ucfirst($log->action) }}</strong> by <strong>{{ $log->user->name }}</strong>
                on <strong>{{ $log->created_at->format('d-m-Y H:i:s') }}</strong>
                <ul>
                    @php
                        $oldData = json_decode($log->old_data, true);
                        $newData = json_decode($log->new_data, true);
                        $productChanges = json_decode($log->product_changes, true);
                    @endphp

                    @if ($oldData && $newData)
                        @foreach ($newData as $key => $newValue)
                            @if (array_key_exists($key, $oldData) && $oldData[$key] != $newValue)
                                <li>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                    <span class="text-danger">{{ $oldData[$key] }}</span>
                                    changed to
                                    <span class="text-success">{{ $newValue }}</span>
                                </li>
                            @endif
                        @endforeach
                    @endif

                    @if ($productChanges)
                        <li><strong>Product Changes:</strong></li>
                        <ul>
                            @foreach ($productChanges as $change)
                                <li>
                                    Product: {{ $change['product_name'] }} -
                                    <span class="{{ $change['action'] == 'added' ? 'text-success' : ($change['action'] == 'removed' ? 'text-danger' : 'text-warning') }}">
                                        {{ ucfirst($change['action']) }}
                                    </span>

                                    @if ($change['action'] == 'updated')
                                        (from {{ $change['old_quantity'] }} to {{ $change['new_quantity'] }})
                                    @elseif ($change['action'] == 'added')
                                        (Quantity: {{ $change['new_quantity'] }})
                                    @elseif ($change['action'] == 'removed')
                                        (Quantity: {{ $change['old_quantity'] }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </ul>
            </li>
        @endforeach
    </ul>
</div>
