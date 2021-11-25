const { registerBlockType } = wp.blocks;
const { Placeholder } = wp.components;
const { __ } = wp.i18n;

registerBlockType("auction-software/my-auctions", {
    title: __("Auction Software My Auctions", "auction-software"),
    description: __("Shows the list of your auctions", "auction-software"),
    icon: "flag",
    category: "auction-software",

    attributes: {
		title: {
			type: 'text',
			default: 'My Auctions',
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
			<p>{__('Auction Software My Auction Widget', 'auction-software')}</p>
		</div>);
    },

    save() {
        return null;
    },
});
