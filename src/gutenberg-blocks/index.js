const { registerBlockType } = wp.blocks;
const { Placeholder } = wp.components;
const { __ } = wp.i18n;
// data for 8 similar blocks with same attributes title, num_of_auctions, hide_time_left
const data = require('./data.json');

// loop through all blocks and register them one by one but with changes such as id, title, description, default attribute title, fallbackifnotselectedtitle.
data.forEach((chunk) => {
	registerBlockType("auction-software/" + chunk.registerBlockType, {
		title: __(chunk.title, "auction-software"),
		description: __(chunk.description, "auction-software"),
		icon: "flag",
		category: "auction-software",
	
		attributes: {
			title: {
				type: 'text',
				default: chunk.attributesTitleDefault,
			},
			num_of_auctions: {
				type: 'text',
				default: 5,
			},
			hide_time_left: {
				type: 'boolean',
				default: false,
			}
		},
	
		edit(props) {
			const handleChange = (type, e) => {
				props.setAttributes({
					[type]: type === 'hide_time_left' ? e.target.checked : e.target.value,
				})
			}
	
			return props.isSelected ? (<div>
				<p>{__('Title', 'auction-software')}</p>
				<input type="text" value={props.attributes.title} onChange={(e) => {handleChange('title', e)}}/>
	
				<p>{__('Number of auctions to show:', 'auction-software')}</p>
				<input type="number" value={props.attributes.num_of_auctions} onChange={(e) => {handleChange('num_of_auctions', e)}}/>
	
				<p>{__('Hide Time Left', 'auction-software')}</p>
				<input type="checkbox" checked={props.attributes.hide_time_left} onChange={(e) => {handleChange('hide_time_left', e)}}/>
			</div>): (<div>
				<p>{__(chunk.fallbackTitleIfNotSelected, 'auction-software')}</p>
			</div>);
		},
	
		save() {
			return null;
		},
	});
})

