import { initAjaxForms } from './core/forms';
import { initActions } from './core/actions';
import { initModals } from './core/dialog';
import { showFlashMessages } from './core/toast';
import { initNavigation } from './frontend/navigation';
import { initLiveSearch } from './frontend/live-search';
import { initTabs } from './frontend/tabs';
import { initFlightWidget, initFlightBoards, initDisruptions } from './frontend/flights';
import { initGalleries } from './frontend/gallery';
import { initGuide } from './frontend/guide';
import { initDirectories } from './frontend/directory';

document.addEventListener('DOMContentLoaded', () => {
    initAjaxForms();
    initActions();
    initModals();
    initNavigation();
    initLiveSearch();
    initTabs();
    initFlightWidget();
    initFlightBoards();
    initDisruptions();
    initGalleries();
    initGuide();
    initDirectories();
    showFlashMessages();
});
