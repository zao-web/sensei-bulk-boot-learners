module.exports = {
	dist: {
		options: {
			processors: [
				require('autoprefixer')({browsers: 'last 2 versions'})
			]
		},
		files: { 
			'assets/css/sensei-bulk-boot-learners.css': [ 'assets/css/src/sensei-bulk-boot-learners.css' ]
		}
	}
};