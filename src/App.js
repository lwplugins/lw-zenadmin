/**
 * Internal dependencies
 */
import LoadError from './components/LoadError';
import Notices from './components/Notices';
import TabSkeleton from './components/TabSkeleton';
import useSettingsStore from './data/useSettingsStore';
import Footer from './shell/Footer';
import navMeta from './shell/navMeta';
import SideNav from './shell/SideNav';
import TopBar from './shell/TopBar';
import { TABS } from './shell/tabs';
import useSaveShortcut from './shell/useSaveShortcut';
import useTab from './shell/useTab';
import useUnsavedWarning from './shell/useUnsavedWarning';
import NoticesTab from './tabs/NoticesTab';
import VisibilityTab from './tabs/VisibilityTab';

// Classic links (?page=lw-zenadmin&tab=menu) open that tab.
const INITIAL_TAB =
	new URLSearchParams( window.location.search ).get( 'tab' ) || 'notices';

/**
 * Shell + one settings store: every tab edits it and one Save (top bar or
 * Cmd/Ctrl+S) writes all changed tabs atomically.
 */
export default function App() {
	const store = useSettingsStore();
	const tab = useTab(
		TABS.map( ( t ) => t.id ),
		INITIAL_TAB
	);
	const current = TABS.find( ( t ) => t.id === tab ) || TABS[ 0 ];

	useUnsavedWarning( store.hasEdits );
	useSaveShortcut( store.save, store.hasEdits && ! store.isSaving, true );

	let content;
	if ( store.error ) {
		content = (
			<LoadError message={ store.error } onRetry={ store.reload } />
		);
	} else if ( store.isLoading ) {
		content = <TabSkeleton />;
	} else if ( current.section ) {
		content = <VisibilityTab tab={ current } store={ store } />;
	} else {
		content = <NoticesTab store={ store } />;
	}

	return (
		<>
			<div className="lw-admin-shell">
				<SideNav
					tabs={ TABS }
					current={ current.id }
					meta={ navMeta( {
						errors: store.errors,
						options: store.draft?.options,
					} ) }
					docsUrl={ store.meta?.docsUrl }
				/>
				<div className="lw-admin-main">
					<TopBar
						title={ current.title }
						store={ store.draft ? store : null }
					/>
					<main className="lw-admin-scroll">
						<div className="lw-admin-content">{ content }</div>
					</main>
					<Footer />
				</div>
			</div>
			<Notices />
		</>
	);
}
