/**
 * WordPress dependencies
 */
import { Card } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	SkeletonBlock,
	SkeletonRegion,
	SkeletonRows,
	SkeletonSection,
	SkeletonText,
} from './skeleton';

/**
 * Placeholder for a tab: the feature state bar and the grouped list card.
 */
export default function TabSkeleton() {
	return (
		<SkeletonRegion className="lw-skel-tab">
			<Card className="lw-admin-section lw-zen-feature">
				<div className="lw-zen-feature__bar">
					<SkeletonBlock width={ 48 } height={ 48 } />
					<span className="lw-skel-stack lw-zen-feature__text">
						<SkeletonText width="40%" size="lg" />
						<SkeletonText width="65%" size="sm" />
					</span>
					<SkeletonBlock width={ 64 } height={ 20 } round />
				</div>
			</Card>
			<SkeletonSection>
				<SkeletonText width="20%" size="sm" />
				<SkeletonRows count={ 6 } />
			</SkeletonSection>
		</SkeletonRegion>
	);
}
