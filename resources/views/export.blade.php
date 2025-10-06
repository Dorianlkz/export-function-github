<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Export Example</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6 bg-gray-100 font-sans">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-8" x-data="{ activeTab: 0 }">
        <h3 class="text-3xl font-bold text-gray-800 mb-8">📋 Form with Stages & Fields</h3>

        <!-- Nav Tabs -->
        <div class="mb-6">
            <nav class="flex flex-wrap gap-2">
                @foreach ($stages as $index => $stage)
                    <button class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200"
                        :class="activeTab === {{ $index }} ?
                            'bg-blue-600 text-white shadow' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-800'"
                        @click="activeTab = {{ $index }}">
                        {{ $stage['name'] ?? 'Unnamed Stage' }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            @foreach ($stages as $index => $stage)
                <div x-show="activeTab === {{ $index }}" class="space-y-6" x-transition>
                    <!-- Stage Title -->
                    <h5 class="text-xl font-semibold text-gray-800 border-b pb-2">
                        {{ $stage['name'] ?? 'Unnamed Stage' }}
                    </h5>

                    <!-- Stage Info -->
                    <div class="grid grid-cols-2 gap-6 text-gray-700">
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="font-semibold">ID</p>
                            <p class="text-gray-500">{{ $stage['id'] ?? '-' }}</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="font-semibold">Type</p>
                            <p class="text-gray-500">{{ $stage['type'] ?? '-' }}</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="font-semibold">Sequence</p>
                            <p class="text-gray-500">{{ $stage['sequence'] ?? '-' }}</p>
                        </div>
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="font-semibold">Duration (days)</p>
                            <p class="text-gray-500">{{ $stage['duration_days'] ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Fields -->
                    @if (!empty($stage['fields']))
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800 mb-4">Fields</h6>
                            <div class="grid gap-4">
                                @foreach ($stage['fields'] as $field)
                                    <div class="p-4 bg-white rounded-lg shadow-sm">
                                        <p class="font-medium text-gray-900">
                                            {{ $field['label'] ?? 'Unnamed Field' }}
                                            <span class="text-sm text-gray-500">(Type:
                                                {{ $field['type'] ?? 'unknown' }})</span>
                                        </p>

                                        @if (!empty($field['options']))
                                            <div class="mt-2 text-sm text-gray-600">
                                                <p class="font-semibold">Options:</p>
                                                <ul class="list-disc list-inside ml-4">
                                                    @foreach ($field['options'] as $option)
                                                        <li>{{ is_array($option) ? json_encode($option) : $option }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Export Button -->
        <div class="mt-8 flex justify-end">
            <a href="{{ route('export.form') }}"
                class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow hover:bg-green-700 transition duration-200">
                ⬇️ Export to Excel
            </a>
        </div>
    </div>
</body>

</html>
