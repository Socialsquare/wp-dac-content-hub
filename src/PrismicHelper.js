import Prismic from 'prismic-javascript';
import PrismicDOM from 'prismic-dom';

export default class {
	constructor(api, settings) {
		this.settings = window.dac_vars || null;
		this.fullTextQuery = this.fullTextQuery.bind(this);
	}

	/**
	 * Full text search query.
	 *
	 * @param {string} path
	 * @param {string} value
	 */
	fullTextQuery(path, value) {
		if (value.length > 2) {
			// Call API.
			return Prismic.api(`${this.settings.api_endpoint}`, {accessToken: this.settings.api_token}).then(api => {
				return api.query(
					[Prismic.Predicates.fulltext(path, value)],
					{pageSize : 10, page : 1, orderings : `[${path} desc]`}
				);
			})
			// Format response.
			.then(response => {
				if (response.results.length) {
					let resultList = {};
					for (let result of response.results) {
						// We have to do different types of calls because fields...
						switch(result.type) {
							case 'case_area':
								resultList[result.id] = PrismicDOM.RichText.asText(result.data.area_name);
								break;
							case 'organisation':
								resultList[result.id] = result.data.name;
								break;
							case 'case-category':
								resultList[result.id] = PrismicDOM.RichText.asText(result.data.name);
								break;
						}
					}
					return resultList;
				}
			}, err => {
				console.error("Something went wrong: ", err);
			});
		}
	}
}
