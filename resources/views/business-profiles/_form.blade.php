<form
    method="POST"
    action="{{ $businessProfile ? route('business-profile.update') : route('business-profile.store') }}"
    enctype="multipart/form-data"
    class="flex flex-col gap-8"
    data-business-profile-form
>
    @csrf
    @if ($businessProfile)
        @method('PUT')
    @endif

    <section class="grid gap-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Business details</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Tell customers what your business offers.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="business_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Business name</label>
                <input id="business_name" name="business_name" type="text" required maxlength="255" value="{{ old('business_name', $businessProfile?->business_name) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('business_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category_id" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Business category</label>
                <select id="category_id" name="category_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">Choose a category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $businessProfile?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="local_government_area_id" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Local government area</label>
                <select id="local_government_area_id" name="local_government_area_id" required data-local-government-area class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">Choose an LGA</option>
                    @foreach ($localGovernmentAreas as $localGovernmentArea)
                        <option value="{{ $localGovernmentArea->id }}" @selected((string) old('local_government_area_id', $selectedLocalGovernmentAreaId) === (string) $localGovernmentArea->id)>{{ $localGovernmentArea->name }}</option>
                    @endforeach
                </select>
                @error('local_government_area_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="town_id" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Town</label>
                <select id="town_id" name="town_id" required data-town-select class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">Choose a town</option>
                    @foreach ($towns as $town)
                        <option
                            value="{{ $town->id }}"
                            data-local-government-area="{{ $town->local_government_area_id }}"
                            @selected((string) old('town_id', $businessProfile?->town_id) === (string) $town->id)
                        >{{ $town->name }}</option>
                    @endforeach
                </select>
                @error('town_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="street_address" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Street address</label>
                <input id="street_address" name="street_address" type="text" maxlength="255" value="{{ old('street_address', $businessProfile?->street_address) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('street_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Business description</label>
                <textarea id="description" name="description" rows="5" maxlength="5000" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('description', $businessProfile?->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="grid gap-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Contact information</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Choose the contact details customers can use to reach you.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="phone" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Phone number</label>
                <input id="phone" name="phone" type="tel" maxlength="30" value="{{ old('phone', $businessProfile?->phone) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="whatsapp" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">WhatsApp number</label>
                <input id="whatsapp" name="whatsapp" type="tel" maxlength="30" value="{{ old('whatsapp', $businessProfile?->whatsapp) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('whatsapp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Business email</label>
                <input id="email" name="email" type="email" maxlength="255" value="{{ old('email', $businessProfile?->email) }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="website" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Website <span class="font-normal text-zinc-500">(optional)</span></label>
                <input id="website" name="website" type="url" maxlength="255" value="{{ old('website', $businessProfile?->website) }}" placeholder="https://" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="grid gap-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Business images</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Add a JPG, PNG, or WebP image. A logo can be up to 2 MB; a cover image can be up to 5 MB.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="logo" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Business logo <span class="font-normal text-zinc-500">(optional)</span></label>
                <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-zinc-700 file:me-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:font-medium file:text-zinc-800 dark:text-zinc-300 dark:file:bg-zinc-800 dark:file:text-zinc-100">
                @if ($businessProfile?->logo_url)
                    <img src="{{ $businessProfile->logo_url }}" alt="Current business logo" class="mt-3 size-20 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                @endif
                @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="cover_image" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Cover image <span class="font-normal text-zinc-500">(optional)</span></label>
                <input id="cover_image" name="cover_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-zinc-700 file:me-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:font-medium file:text-zinc-800 dark:text-zinc-300 dark:file:bg-zinc-800 dark:file:text-zinc-100">
                @if ($businessProfile?->cover_image_url)
                    <img src="{{ $businessProfile->cover_image_url }}" alt="Current business cover image" class="mt-3 h-24 w-full rounded-lg border border-zinc-200 object-cover dark:border-zinc-700">
                @endif
                @error('cover_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('business-profile.show') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancel</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
            {{ $businessProfile ? 'Save changes' : 'Submit for approval' }}
        </button>
    </div>
</form>
