module.exports = {
	main: {
		options: {
			mode: 'zip',
			archive: './release/senseiboot.<%= pkg.version %>.zip'
		},
		expand: true,
		cwd: 'release/<%= pkg.version %>/',
		src: ['**/*'],
		dest: 'senseiboot/'
	}
};