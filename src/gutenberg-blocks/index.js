const { registerBlockType } = wp.blocks;
const { Placeholder } = wp.components;
const { __ } = wp.i18n;
// data for 8 similar blocks with same attributes title, num_of_auctions, hide_time_left
const data = require('./data.json');

// loop through all blocks and register them one by one but with changes such as id, title, description, default attribute title, fallbackifnotselectedtitle.
data.forEach((chunk) => {
	registerBlockType("auction-software/" + chunk.register_block_type, {
		title: __(chunk.title, "auction-software"),
		description: __(chunk.description, "auction-software"),
		icon: "flag",
		category: "auction_software",
	
		attributes: {
			title: {
				type: 'text',
				default: chunk.attributes_title_default,
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
	
			return props.isSelected ? (<div style={{padding: "10px"}}>
				<div style={{marginBottom: "10px"}}>
					<span style={{marginBottom: "5px", display: "block"}}>{__('Title:', 'auction-software')}</span>
					<input style={{width: "35%"}} type="text" value={props.attributes.title} onChange={(e) => {handleChange('title', e)}}/>
				</div>
	
				<div style={{marginBottom: "10px"}}>
					<span style={{marginBottom: "5px", display: "block"}}>{__('Number of auctions to show:', 'auction-software')}</span>
					<input style={{width: "35%"}} type="number" value={props.attributes.num_of_auctions} onChange={(e) => {handleChange('num_of_auctions', e)}}/>
				</div>

				<div style={{marginBottom: "10px"}}>
					<span style={{marginRight: "10px"}}>{__('Hide Time Left:', 'auction-software')}</span>
					<input type="checkbox" checked={props.attributes.hide_time_left} onChange={(e) => {handleChange('hide_time_left', e)}}/>
				</div>
			</div>): (<div style={{padding: "10px", border: "1px solid black"}}>
				<p>{__(chunk.fallbackTitleIfNotSelected, 'auction-software')}</p>
			</div>);
		},
	
		save() {
			return null;
		},
	});
})

