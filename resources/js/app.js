document.addEventListener('change', (event) => {
    if (!(event.target instanceof HTMLSelectElement) || !event.target.matches('[data-local-government-area]')) {
        return;
    }

    const form = event.target.closest('[data-business-profile-form]');
    const townSelect = form?.querySelector('[data-town-select]');

    if (!(townSelect instanceof HTMLSelectElement)) {
        return;
    }

    const selectedArea = event.target.value;
    let firstAvailableTown = null;

    for (const option of townSelect.options) {
        const isAvailable = option.value !== '' && option.dataset.localGovernmentArea === selectedArea;

        option.hidden = option.value !== '' && !isAvailable;
        option.disabled = option.value !== '' && !isAvailable;

        if (isAvailable && firstAvailableTown === null) {
            firstAvailableTown = option;
        }
    }

    if (!townSelect.selectedOptions[0] || townSelect.selectedOptions[0].disabled) {
        townSelect.value = firstAvailableTown?.value ?? '';
    }

    townSelect.disabled = selectedArea === '' || firstAvailableTown === null;
});

document.querySelectorAll('[data-business-profile-form]').forEach((form) => {
    const areaSelect = form.querySelector('[data-local-government-area]');

    if (areaSelect instanceof HTMLSelectElement) {
        areaSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('[data-business-profile-form]').forEach((form) => {
        const areaSelect = form.querySelector('[data-local-government-area]');

        if (areaSelect instanceof HTMLSelectElement) {
            areaSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
});

function updateDirectoryTownOptions(form) {
    const areaSelect = form.querySelector('[data-directory-lga]');
    const townSelect = form.querySelector('[data-directory-town]');

    if (!(areaSelect instanceof HTMLSelectElement) || !(townSelect instanceof HTMLSelectElement)) {
        return;
    }

    const selectedArea = areaSelect.value;
    let hasAvailableTown = false;

    for (const option of townSelect.options) {
        const isAvailable = option.value !== ''
            && (selectedArea === '' || option.dataset.localGovernmentArea === selectedArea);

        option.hidden = option.value !== '' && !isAvailable;
        option.disabled = option.value !== '' && !isAvailable;
        hasAvailableTown ||= isAvailable;
    }

    if (townSelect.selectedOptions[0]?.disabled) {
        townSelect.value = '';
    }

    townSelect.disabled = selectedArea !== '' && !hasAvailableTown;
}

document.addEventListener('change', (event) => {
    if (!(event.target instanceof HTMLSelectElement) || !event.target.matches('[data-directory-lga]')) {
        return;
    }

    const form = event.target.closest('[data-directory-filter-form]');

    if (form instanceof HTMLFormElement) {
        updateDirectoryTownOptions(form);
    }
});

document.querySelectorAll('[data-directory-filter-form]').forEach(updateDirectoryTownOptions);

document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('[data-directory-filter-form]').forEach(updateDirectoryTownOptions);
});

document.addEventListener('submit', (event) => {
    if (!(event.target instanceof HTMLFormElement) || !event.target.matches('[data-google-registration-form]')) {
        return;
    }

    const registrationForm = document.querySelector('[data-registration-form]');
    const googleUsername = event.target.querySelector('[name="username"]');
    const googleRole = event.target.querySelector('[name="role"]');
    const username = registrationForm?.querySelector('[name="username"]');
    const role = registrationForm?.querySelector('[data-registration-role]');

    if (googleUsername instanceof HTMLInputElement && googleRole instanceof HTMLInputElement
        && username instanceof HTMLInputElement && role instanceof HTMLSelectElement) {
        googleUsername.value = username.value;
        googleRole.value = role.value;
    }
});