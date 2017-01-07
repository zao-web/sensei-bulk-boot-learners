module.exports = {
	main: {
		options: {
			mode: 'zip',
			archive: './release/sensei-bulk-boot-learners.<%= pkg.version %>.zip'
		},
		expand: true,
		cwd: 'release/<%= pkg.version %>/',
		src: ['**/*'],
		dest: 'sensei-bulk-boot-learners/'
	}
};
